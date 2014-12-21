<?php
/**
 * Flox Core API Class
 */
class Flox_Core_Api
{
    private $_api_id;
    private $_config = array();
    private $_closure = NULL;
    public static $loader;
    public static $saver;

    private static $_instance = array();

    public static function instance($api_id, $config = array())
    {
        $api_id = (string) $api_id;
        if (! isset(self::$_instance[$api_id])) {
            self::$_instance[$api_id] = new self($api_id, $config);
        }
        return self::$_instance[$api_id];
    }

    public function __construct($api_id, $config)
    {
        $this->_api_id = $api_id;
        $this->load($config);
    }

    public function load($config = array(), & $errstr = '')
    { 
        try {
            if (! $config && is_callable(Flox_Api::$loader)) {
                $config = call_user_func_array(
                    Flox_Api::$loader, 
                    array($this->_api_id)
                );
                if (! is_array($config)) {
                    $config = array();
                }
            }

            $config = $config + array(
                'title' => 'Unkown API',
                'in' => array(),
                'out' => array(),
                'expr' => '',
            );
            $config['id'] = $this->_api_id;

            $ps = array();
            foreach ($config['in'] as $idx => $p) {
                if (! isset($p['name'])) {
                    throw new Exception('invalid input param name');
                }
                if (! preg_match('/^[a-zA-Z_]+/i', $p['name'])) {
                    throw new Exception('invalid input param name');
                }

                if (! isset($p['title']) || ! $p['title']) {
                    $config['in'][$idx]['title'] = $p['name'];
                }

                $ps[] = '$' . $p['name'];
            }
            $param = implode(', ', $ps);

            foreach ($config['out'] as $idx => $p) {
                if (! isset($p['name'])) {
                    throw new Exception('invalid output param name');
                }
                if (! preg_match('/^[a-zA-Z_]+/i', $p['name'])) {
                    throw new Exception('invalid output param name');
                }

                if (! isset($p['title']) || ! $p['title']) {
                    $config['out'][$idx]['title'] = $p['name'];
                }

                if (! isset($p['path']) || ! is_string($p['path'])) {
                    $config['out'][$idx]['path'] = '';
                }
            }

            $closure = create_function($param, $config['expr']);
            if ($err = error_get_last()) {
                throw new Exception("failed to create closure, "
                    . "line {$err['line']}, message: {$err['message']}");
            }

            $this->_closure = $closure;
            $this->_config = $config;

            return TRUE;
        } catch (Exception $e) {
            $errstr = $e->getMessage();
        }
    }

    public function config()
    {
        return $this->_config;
    }
    
    public function save()
    {
        if (is_callable(Flox_Api::$saver)) {
            call_user_func_array(Flox_Api::$saver, array(
                $this->_api_id,
                $this->_config
            ));
        }
    }

    public function execute($arg = array())
    {
        $args = array();
        foreach ($this->_config['in'] as $param) {
            $key = $param['name'];
            $args[$key] = isset($arg[$key])? $arg[$key]: NULL;
        }
        
        $result = call_user_func_array($this->_closure, $args);
        if ($err = error_get_last()) {
            throw new Flox_Exception("Error when execute closure: " . $err['message']);    
        }

        $ret = array();
        foreach ($this->_config['out'] as $out) {
            $key = $out['name'];
            $ret[$key] = Flox_Util::path($result, $out['path']);
        }

        return $ret;
    }
}

