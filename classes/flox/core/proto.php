<?php
/**
 * proto.php 操作原型
 */

class Flox_Core_Proto
{
    private $_config = array();

    private $_closure;

    public static function factory($config)
    {
        return new self($config);
    }

    public function __construct($config)
    {
        $this->set_config($config);
    }

    public function set_config($config)
    {
        if (is_string($config)) {
            $config = Flox_Loader::load('proto', $config);
        }

        if (is_array($config)) {
            $this->_config = $config;
        }

        $this->_config = $this->_config + array(
            'id' => '',
            'expr' => '',
            'param' => array(
                0 => array(
                    'name' => 'qq',
                    'title' => '用户QQ号码',
                    'default' => '',
                    'type' => '', // text select textarea
                ),    
            ),    
            'return' => array(
                0 => array(
                    'name' => 'dbname',
                    'title' => '数据库名',
                    'type' => '', // string array obj    
                ),    
            ),
        );

        if (! $this->_config['expr']) {
            throw new Flox_Exception("proto config must have expr");
        }
        $closure_id = md5($this->_config['expr']);

        Flox::profile("Proto expr, ". print_r($this->_config['expr'], TRUE)." closure_id: ". $closure_id, __FILE__, __LINE__);
        if (! $closure = Flox::current()->get_closure($closure_id)) {

            $params = array();
            foreach ($this->_config['param'] as $param) {
                $params[] = '$' . $param['name'];
            }
            $param_string = implode(', ', $params);
            if (! $closure = create_function($param_string, $this->_config['expr'])) {
                Flox::profile("failed to create closure", __FILE__, __LINE__);
            }
            
            Flox::current()->set_closure($closure_id, $closure);
        }

        $this->_closure = $closure;
    }

    public function get_config()
    {
        return $this->_config;
    }

    public function execute($arg = array())
    {
        Flox::current()->set_current_proto($this);
        $ret = call_user_func_array($this->_closure, $arg);
    }
}
