<?php
/**
 * Flox Core Flow Class
 */
class Flox_Core_Flow
{
    public static $config = array();
    public static $loader;
    public static $saver;
    public static $current;
    public static $_flow_stack = array();

    private $_config = array();
    public $context = array();
    private $_flow_id;
    private $_node = array();


    public static function factory($flow_id, $config = array())
    {
        return new self($flow_id, $config);
    }

    public static function in_flow($flow)
    {
        self::$_flow_stack[] = $flow;
        self::$current = $flow;
    }

    public static function out_flow()
    {
        array_pop(self::$_flow_stack);
        self::$current = end(self::$_flow_stack);
    }

    public function __construct($flow_id, $config)
    {
        $this->_flow_id = $flow_id;

        if (isset(Flox_Flow::$config[$flow_id])) {
            $config = Flox_Flow::$config[$flow_id];
        }

        if (! $this->load($config, $errstr)) {
            throw new Exception("{$errstr}");
        }
        
    }

    public function load($config = array(), & $errstr = '')
    {
        try {
            if (! $config && is_callable(Flox_Flow::$loader)) {
                $config = call_user_func_array(Flox_Flow::$loader, array(
                    $this->_flow_id
                ));

                if (! is_array($config)) {
                    $config = array();
                }
            }

            $config = $config + array(
                'title' => 'Unkown Flow',
                'in' => array(),
                'out' => array(),
                'entry' => '',
                'node' => array(),
            );
            $config['id'] = $this->_flow_id;


            if ($config['node'] && ! isset($config['node'][$config['entry']])) {
                throw new Exception("entry id not found: " . $config['entry']);
            }

            foreach ($config['node'] as $node_id => $node) {
                $config['node'][$node_id]['id'] = $node_id;
                if (! isset($node['title']) || ! $node['title']) {
                    $config['node'][$node_id]['title'] = $node_id;
                }

                if (! isset($node['api'])) {
                    $config['node'][$node_id]['api'] = $node['api'] = '';
                }
                Flox_Api::instance($node['api']);
                if (! isset($node['arg'])) {
                    $config['node'][$node_id]['arg'] = $node['arg'] = array();
                }
                if (! isset($node['bind'])) {
                    $config['node'][$node_id]['bind'] = $node['bind'] = array();
                }
                if (! isset($node['direction'])) {
                    $config['node'][$node_id]['direction']
                        = $node['direction'] = array();
                }

                foreach ($node['bind'] as $idx => $b) {
                    if (! isset($b['source'])) {
                        $config['node'][$node_id]['bind'][$idx]['source'] = '';
                    }
                    if (! isset($b['target'])) {
                        $config['node'][$node_id]['bind'][$idx]['target'] 
                            = $b['target'] = '';
                    }
                    if (! preg_match('/^[a-zA-Z_]+/i', $b['target'])) {
                        throw new Exception("invalid target name");
                    }
                    if (! isset($b['title']) || ! $b['title']) {
                        $config['node'][$node_id]['bind'][$idx]['title']
                            = $b['title'] = $b['target'];
                    }

                }

                foreach ($node['direction'] as $idx => $d) {
                    if (! isset($d['next'])) {
                        throw new Exception("invalid next node of direction");
                    }
                    if (! isset($config['node'][$d['next']])) {
                        throw new Exception("invalid next node of direction");
                    }
                    if (! isset($d['title'])) {
                        $config['node'][$node_id]['direction'][$idx]['title']
                            = $d['title'] = $idx;
                    }
                    if (! isset($d['expr'])) {
                        $config['node'][$node_id]['direction'][$idx]['expr']
                            = $d['expr'] = '';
                    }
                    $d['_expr'] = $d['expr'];
                    $d['_expr'] = '$CONTEXT = Flox_Flow::$current->context;'
                        . "return ({$d['_expr']});";
                    $config['node'][$node_id]['direction'][$idx]['_expr']
                        = $d['_expr'];
                        
                    Flox_Util::create_closure('$context', $d['_expr']);
                }
            }

            foreach ($config['in'] as $idx => $p) {
                if (! isset($p['name'])) {
                    throw new Exception("invalid input param name");
                }
                if (! preg_match('/^[a-zA-Z_]+/i', $p['name'])) {
                    throw new Exception("invalid input parma name");
                }
                if (! isset($p['title']) || ! $p['title']) {
                    $config['in'][$idx]['title'] = $p['name'];
                }
            }

            foreach ($config['out'] as $idx => $p) {
                if (! isset($p['name'])) {
                    throw new Exception("invalid input param name");
                }
                if (! preg_match('/^[a-zA-Z_]+/i', $p['name'])) {
                    throw new Exception("invalid input parma name");
                }
                if (! isset($p['path'])) {
                    $config['out'][$idx]['path'] = '';
                }
                if (! isset($p['title']) || ! $p['title']) {
                    $config['out'][$idx]['title'] = $p['name'];
                }
            }

            Flox_Flow::$config[$this->_flow_id] = $this->_config = $config;

            return TRUE;
        } catch (Exception $e) {
            $errstr = $e->getMessage();
        }
    }

    public function save()
    {
        if (is_callable(Flox_Flow::$saver)) {
            call_user_func_array(Flox_Flow::$saver, array(
                $this->_flow_id,
                $this->_config
            ));
        }
    }

    private function _execute_node($node_id)
    {
        $node = $this->_config['node'][$node_id];
        $api = Flox_Api::instance($node['api']);
        $ret = $api->execute($node['arg']);

        foreach ($node['bind'] as $bind) {
            $value = Flox_Util::path($ret, $bind['source']);
            $this->context[$bind['target']] = $value;
        }

        foreach ($node['direction'] as $idx => $d) {
            $closure = Flox_Util::create_closure('$context', 
                        "{$d['_expr']}");

            if (@call_user_func_array($closure, array($this->context))) {
                if ($err = error_get_last()) {
                    throw new Flox_Exception("Error when execute direction:\r\n"
                        ."Flow:\t{$this->_flow_id}\r\n"
                        ."Node:\t{$node_id}\r\n"
                        ."direction:\t{$idx}\t{$d['title']}\r\n"
                        ."expr:\t{$d['expr']}\r\n"
                        ."err:\t{$err['message']}\r\n");
                }
                return $d['next'];
            }
        }
    }

    public function config()
    {
        return $this->_config;
    }
    
    /**
     * init context when execute
     */
    public function execute($arg = array())
    {
        Flox_Flow::in_flow($this);
        $this->context = array();

        foreach ($this->_config['in'] as $in) {
            $key = $in['name'];
            $this->context[$key] = isset($arg[$key])? $arg[$key] : NULL;
        }

        $node_id = $this->_config['entry'];
        while (isset($this->_config['node'][$node_id])) {
            $node_id = $this->_execute_node($node_id);
        }

        Flox_Flow::out_flow();

        if (Flox_Flow::$current) {
            foreach ($this->_config['out'] as $out) {
                $key = $out['name'];
                $value = Flox_Util::path($this->context, $out['path']);
                Flox_Flow::$current->context[$key] = $value;
            }
        }
    }


}
