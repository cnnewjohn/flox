<?php
/**
 * entity.php 流程节点
 */

class Flow_Core_Entity
{
    private $_config = array();

    public static function factory($config)
    {
        return new self($config);

    }

    public function __construct($config)
    {
        $this->set_config($config);
    }

    /**
     * 加载节点配置
     */
    public function set_config($config)
    {
        if (is_string($config)) {
            $config = Flox_Loader::load('entity', $config);
        }

        if ($config instanceof $this) {
            $config = $config->get_config();
        }

        if (is_array($config)) {
            $config = $config + array(
                'id' => '',
                'type' => ''
                'proto' => '',
                'arg' => '',
                'device' => array(
                    0 => array(
                        'type' => '', // direct 直接跳转，dsl 特定领域语言, expr PHP表达式
                        'dsl' => '',
                        'expr' => '',
                        'next' => '',    
                    ),    
                ),
            );
        }
    }

    public function get_config()
    {
        return $this->_config;
    }


    /**
     * 执行处理节点
     */
    public function execute()
    {
        if ($config['type'] == 'process') { 
            $proto = Flox_Proto::factory($this->_config['proto']);
            call_user_func_array($proto, $this->_config['arg']);
        }

        foreach ($this->_config['device'] as $device) {
            if ($device['type'] == 'direct') {
                return $device['next'];
            } elseif ($device['type'] == 'dsl') {

            } elseif ($device['type'] == 'expr') {
                $closure_id = md5($device['expr']);
                if (! $closure = Flox::current()->get_closure($closure_id)) {
                    $closure = create_function('', $expr);
                    Flox::current()->set_closure($closure_id, $closure);
                }
                $device_flag = call_user_func_array($closure, array());
                if ($device_flag) {
                    return $device['next'];
                }
            }
        }
    }

}
