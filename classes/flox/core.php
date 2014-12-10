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

    private $_current_entity;

    private $_current_proto;

    /**
     * 闭包函数数组
     */
    private $_closure = array();

    public static $profile = TRUE;
    private static $_profile = array();
    private static $_profile_callback;

    /**
     * 工厂方法，创建一个新的流程引擎实例
     *
     * @param mixed $flow, 流程。如果为字符串，将视为流程id，并自动加载流程；如果为数组，将设为流程配置；如果为对象，将视为流程实例
     */
    public static function factory($flow)
    {
        Flox::profile("Flox::factory, " . print_r($flow, TRUE), __FILE__, __LINE__);
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
        $this->_flow = Flox_Flow::factory($flow);
        
    }

    /**
     * 获取加载的流程实例
     */
    public function get_flow()
    {
        return $this->_flow;
    }

    public function set_current_flow($flow)
    {
        $this->_current_flow = $flow;
    }

    public function get_current_flow()
    {
        return $this->_current_flow;
    }

    public function set_current_entity($entity)
    {
        $this->_current_entity = $entity;
    }
    public function get_current_entity()
    {
        return $this->_current_entity;
    }

    public function set_current_proto($proto)
    {
        $this->_current_proto = $proto;
    }
    public function get_current_proto()
    {
        return $this->_current_proto;
    }
    /**
     * 启动当前引擎
     */
    public function execute($arg = array())
    {
        Flox::set_current($this);

        $this->_flow->execute($arg);

        Flox::set_current(NULL);
    }

    public function set_closure($closure_id, $closure)
    {
        $this->_closure[$closure_id] = $closure;
    }

    public function get_closure($closure_id)
    {
        return isset($this->_closure[$closure_id]) ? $this->_closure[$closure_id] : NULL;
    }

    /**
     * 设置分析回调函数
     */
    public static function set_profile($callback)
    {
        self::$_profile_callback = $callback;
    }

    public static function profile($msg, $file = NULL, $line = NULL)
    {
        if (! self::$profile) {
            return;
        }

        if (self::$_profile_callback) {
            call_user_func_array(self::$_profile_callback, array($msg, $file, $line));
            return;
        }

        $this->_profile[] = array(
            'time' => microtime(TRUE),
            'msg' => $msg,
            'file' => $file,
            'line' => $line,
        );
    }
}


