<?php
/**
 * proto.php Flox Proto Class 
 */
class Flox_Core_Proto
{
    /**
     * proto instance array
     */
    private static $_instance = array();

    /**
     * cached proto config
     */
    private static $_cached_config = array();

    /**
     * proto custom loader
     */
    private static $_loader = array();

    /**
     * proto closure function
     */
    private $_closure;

    /**
     * proto closure param
     */
    private $_param = array();

    /**
     * get proto instance by proto id
     *
     * @param string $id, proto id
     * @param mixed $config, proto config
     * @return object
     */
    public static function instance($id, $config = NULL)
    {
        if (! $id || ! is_string($id)) {
            throw new Flox_Exception("Proto id must be string");
        }

        if (! isset(self::$_instance[$id])) {
            self::$_instance[$id] = new self($id, $config);
        }

        return self::$_instance[$id];
    }

    /**
     * set custom proto loader
     *
     * @param string $id, loader id
     * @param callback $loader, custom loader
     */
    public static function set_loader($id, $loader)
    {
        if (! $id || ! is_string($id)) {
            throw new Flox_Exception("Loader id must be string");
        }

        if (! is_callable($loader)) {
            throw new Flox_Exception("Loader must be callback");
        }

        self::$_loader[$id] = $loader;
    }

    /**
     * load proto by custom loader
     *
     * @param string $id, proto id
     * @return mixed, array || NULL
     */
    public static function load($id)
    {
        foreach (self::$_loader as $loader) {
            $config = $loader($id);
            if ($config && is_array($config)) {
                return $config;
            }
        }
    }

    /**
     * construct
     */
    public function __construct($id, $config)
    {
        if (! $id || ! is_string($id)) {
            throw new Flox_Exception("Proto id must be string");
        }

        if (! $config || ! is_array($config)) {
            $config = Flox_Proto::load($id);
            if (! $config || ! is_array($config)) {
                throw new Flox_Exception("Failed to load proto config");
            }
        }

        if (! $config || ! is_array($config)) {
            throw new Flox_Exception("Proto config must be array");
        }

        /**
        array(
            'expr' => '',
            'param' => array(
                array(
                    'name' => 'param1',
                    'default' => '',
                ),
            ),
        )
         */

        if (! isset($config['expr']) || ! is_string($config['expr'])) {
            throw new Flox_Exception("Proto expr must be string");
        }

        if (! Flox_Util::check_php_syntax($config['expr'])) {
            throw new Flox_Exception("Proto expr is invalid source php code");
        }

        if (! isset($config['param'])) {
            $config['param'] = array();
        }

        $param_keys = array();

        foreach ($config['param'] as $idx => $param) {
            if (! isset($param['name']) || ! is_string($param['name']) 
                || ! $param['name']) {
                throw new Flox_Exception("Proto param name must be string");
            }

            if (! isset($param['default'])) {
                $config['param'][$idx]['default'] = $param['default'] = NULL;
            }

            $param_keys[] = '$' . $param['name'];
        }

        $this->_param = $config['param'];

        $param_string = implode(', ', $param_keys);
        $closure = create_function($param_string, $config['expr']);
        
        if (! $closure) {
            throw new Flox_Exception("Failed to craete proto closure");
        }

        $this->_closure = $closure;

    }

    /**
     * execute proto closure
     *
     * @param array $arg, for closure
     */
    public function execute($arg = array())
    {
        if (! is_array($arg)) {
            $arg = array();
        }
        
        $value = array();
        foreach ($this->_param as $param) {
            $key = $param['name'];
            $value[$key] = isset($arg[$key]) ? $arg[$key] : $param['default'];
            $value[$key] = Flox::replace_var($value[$key]);
        }

        return call_user_func_array($this->_closure, $value);
    }

}
