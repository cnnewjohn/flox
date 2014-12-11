<?php
/**
 * core.php Flox Core Class 
 */
class Flox_Core
{

    /**
     * current running flox instance 
     */
    private static $_current_flox;

    /**
     * init flow 
     */
    private $_init_flow;

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
     * create new flox instance 
     *
     * @param string $id, flow id
     * @return object, flox instance 
     */
    public static function factory($id)
    {
        return new self($id);
    }

    /**
     * return current running flox instance 
     */
    public static function current()
    {
        return self::$_current_flox;
    }

    /**
     * set current running flox instance when execute 
     */
    public static function set_current(Flox_Core $flox)
    {
        self::$_current_flox = $flox;
    }

    /**
     * construct
     *
     * @param string $id, flow id
     */
    public function __construct($id)
    {
        $this->_init_flow = Flox_Flow::factory($id);
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
     * execute flow instance
     *
     * @param array $arg, flow param value 
     */
    public function execute($arg = array())
    {
        Flox::set_current($this);
        $this->_init_flow->execute($arg);
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


