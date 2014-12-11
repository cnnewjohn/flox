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
     * current flox context
     */
    private $_context = array();

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
     * get global var in flox instance
     */
    public static function get_global($key)
    {
        return Flox::current()->get_context($key);
    }

    public static function set_global($key, $value)
    {
        Flox::current()->set_context($key, $value);
    }

    /**
     * get var in current flow instance
     */
    public static function get_var($key)
    {
        return Flox::current()->get_current_flow()->get_context($key);
    }

    public static function set_var($key, $value)
    {
        Flox::current()->get_current_flow()->set_context($key, $value);
    }

    /**
     * replace context
     */
    public static function replace_var($var, $scope = 'ALL')
    {
        /**
        $reg = '/\{\{(GLOBAL|VAR)[0-9a-zA-Z-_\.]*\}\}/';
        if (preg_match_all($reg, $var, $m)) {
            foreach ($m[0] as $source) {
                $path = explode('.', $source);
                $depth = 0;
                $max = count($path);
                do {
                    $value = ''; 
                    $depth ++;
                } while ($depth >= $max);
                $value = '';
                $var = str_replace($source, $value, $var);
            }
        }
         */
        return $var;
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

    public function set_context($key, $value)
    {
        $this->_context[$key] = $value;
    }

    public function get_context($key)
    {
        if (isset($this->_context[$key])) {
            return $this->_context[$key];
        }
    }

    public function set_current_flow($flow)
    {
        $this->_current_flow = $flow;
    }

    public function get_current_flow()
    {
        return $this->_current_flow;
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


