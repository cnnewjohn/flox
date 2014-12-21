<?php
/**
 * Flox Node Core class
 */
class Flox_Core_Node
{
    private static $_instance = array();
    private $_flow_id;
    private $_node_id;
    private $_config = array();
    private $_closure = array();

    public static function instance($flow_id, $node_id)
    {
        if (! isset(self::$_instance[$flow_id])) {
            self::$_instance[$flow_id] = array();
        }

        if (! isset(self::$_instance[$flow_id][$node_id])) {
            self::$_instance[$flow_id][$node_id] =
                new self($flow_id, $node_id);
        }

        return self::$_instance[$flow_id][$node_id];
    }

    public function __construct($flow_id, $node_id)
    {
        $this->_flow_id = $flow_id;
        $this->_node_id = $node_id;
        $this->_config = array(
            'id' => $node_id,
            'bind' => array(),
            'direction' => array(),
        );
    }

    public function api($api_id)
    {
        $this->_config['api'] = $api_id;
        return $this;
    }

    public function arg($arg = array())
    {
        $this->_config['arg'] = $arg;
        return $this;
    }

    public function bind($bind)
    {
        $this->_config['bind'][] = $bind;
    }

    public function direction($direction)
    {
        $expr = $direction['expr'];
        if (! $closure = create_function('$context', "return ({$direction['expr']});")) {
            $err = error_get_last();
            throw new Flox_Exception("Erron on create direction expr: {$err['message']}\r\n{$direction['expr']}\r\n");
        }
        $this->_closure[] = $closure;
        $this->_config['direction'][] = $direction;
    }

    public function execute()
    {
        $api = Flox_Api::instance($this->_config['api']);
        $ret = $api->execute($this->_config['arg']);
        foreach ($this->_config['bind'] as $bind) {
            $key = $bind['target'];
            $value = Flox_Util::path($ret, $bind['source']);
            //Flox_Flow::current()->context[$key] = $value;
        }

        $next = NULL;
        foreach ($this->_closure as $idx => $closure) {
            if (call_user_func_array($closure, array($context))) {
                $next = $this->_config['direction'][$idx];
                break;
            }
        }

        return $next;
    }


}
