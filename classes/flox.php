<?php
/**
 * flox.php 流程引擎
 */

class Flox
{
    /**
     * 允许实例化多个引擎，但一个进程同时只有一个引擎处于执行状态 
     */
    public static $current;

    /**
     * 当前实例加载的流程id和流程配置
     */
    private $_flow_id;
    private $_flow_config;

    /**
     * 当前正在执行的流程ID和实例
     */
    private $_current_flow_id;
    private $_current_flow;

    private $_current_entity_id;
    private $_current_entity;

    private $_current_proto_id;
    private $_current_proto;

    /**
     * 闭包数组
     */
    private $_closure = array();
    
    /**
     * 当前正在执行的流程全路径，即从顶级流程到当前流程的ID路径。数组，栈结构
     */
    private $_current_flow_path = array();

    /**
     * 创建一个引擎实例
     *
     * @param string $flow_id, 流程ID，可选。为空则自动生成
     * @param array $flow_config, 流程配置，可选。为空则根据流程ID自动加载
     * @return object, 引擎实例
     */
    public static function factory($flow_id = NULL, $flow_config = array())
    {
        return new self($flow_id, $flow_config);
    }

    /**
     * 引擎内部流程操作获取当前正在执行的引擎实例
     */
    public static function current()
    {
        return self::$current;
    }

    /**
     * 构造函数，初始化
     */
    public function __construct($flow_id, $flow_config)
    {
        if (! $flow_id) {
            $list($usec, $sec) = explode(' ', microtime());
            $usec = intval($usec * pow(10, 8));
            $flow_id = date('YmdHis') . "_{$usec}";
        }
        $this->_flow_id = $flow_id;

        if (! $flow_config) {
            $flow_config = array();
        }
        $this->_flow_config = $flow_config;
    }

    /**
     * 获取当前流程全路径
     */
    public function get_current_flow_path()
    {
        return $this->_current_flow_path;
    }
    
    /**
     * 获取当前正在执行的流程ID
     */
    public function get_current_flow_id()
    {
        return $this->_current_flow_id;
    }
    
    /**
     * 获取当前正在执行的流程实例
     */
    public function get_current_flow()
    {
        return $this->_current_flow;
    }

    public function set_current_flow($flow_instance)
    {
        $this->_current_flow = $flow_instance;
    }


    /**
     * 获取当前正在执行的节点实例
     */
    public function get_current_entity()
    {
    }

    /**
     * 获取当前正在执行的操作原型实例
     */
    public function get_current_proto()
    {
    }

    /**
     * 进入流程
     */
    public function enter_flow($flow_id)
    {
        array_push($this->_current_flow_path, $flow_id);
    }

    /**
     * 退出流程
     */
    public function exit_flow()
    {
        array_pop($this->_current_flow_path);
    }

    /**
     * 设置匿名闭包
     */
    public function set_closure($closure_id, $closure)
    {
        $this->_closure[$closure_id] = $closure;
    }

    /**
     * 获取匿名闭包
     */
    public function get_closure($closure_id)
    {
        if (isset($this->_closure[$closure_id])) {
            return $this->_closure[$closure_id];
        }
    }

    /**
     * 执行指定流程
     */
    public function execute()
    {
        /**
         * 设置当前引擎为执行状态
         */   
        Flox::$current = $this;

        /**
         * 执行流程
         */
        $flow = Flox_Flow::factory($flow_id, $flow_config);
        $flow->execute();
    }

}
