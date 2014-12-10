<?php
/**
 * core.php 流程引擎内核
 */

class Flox_Core
{

    /**
     * 当前正在执行的引擎实例
     */
    private static $_current;

    /**
     * 当前引擎加载的流程实例
     */
    private $_flow;

    /**
     * 当前引擎正在执行的流程实例
     */
    private $_current_flow;

    /**
     * 闭包函数数组
     */
    private $_closure = array();

    /**
     * 工厂方法，创建一个新的流程引擎实例
     *
     * @param mixed $flow, 流程。如果为字符串，将视为流程id，并自动加载流程；如果为数组，将设为流程配置；如果为对象，将视为流程实例
     */
    public static function factory($flow)
    {
        return new self($flow);
    }

    /**
     * 获取当前正在执行的流程引擎实例
     */
    public static function current()
    {
        return self::$_current;
    }

    /**
     * 设置当前正在执行的流程引擎实例，仅供引擎启动时调用
     */
    public static function set_current($flox = NULL)
    {
        self::$_current = $flox;
    }



    public function __construct($flow)
    {
        $this->set_flow($flow);
    }

    /**
     * 设置要加载执行的流程实例
     *
     */
    public function set_flow($flow)
    {
        
        if (is_string($flow) || is_array($flow)) {
            $flow = Flox_Flow::factory($flow);
        }

        if ($flow instanceof Flox_Flow) {
            $this->_flow = $flow;
        }
        
    }

    /**
     * 获取加载的流程实例
     */
    public function get_flow()
    {
        return $this->_flow;
    }


    /**
     * 启动当前引擎
     */
    public function execute()
    {
        Flox::set_current($this);

        $this->_flow->execute();

        Flox::set_current(NULL);
    }

    public function set_closure($closure_id, $closure)
    {
        $this->_closure[$closure_id] = $closure;
    }

    public function get_closure($closure_id)
    {
        return $this->_closure[$closure_id];
    }
}
