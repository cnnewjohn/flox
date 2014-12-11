<?php
/**
 * Flow Class
 */
class Flox_Core_Flow
{

    /**
     * current flow instance config
     */
    private $_config = array();

    /**
     * current flow isntance id
     */
    private $_id = '';

    /**
     * cached flow instance config
     */
    private static $_cached_config = array();

    /**
     * flow config custom loader
     */
    private static $_loader = array();

    /**
     * decesion expr closure function
     */
    private static $_closure = array();

    /**
     * create new flow instance
     *
     * @param mixed $id, flow id
     * @param mixed $config, flow config
     */
    public static function factory($id, $config = NULL)
    {
        return new self($id, $config);
    }

    /**
     * cache flow config by flow id
     *
     * @param string $id, flow id
     * @param arrray $config, flow config
     * @return NULL
     */
    public static function set_cached_config($id, $config)
    {
        self::$_cached_config[$id] = $config;
    }

    /**
     * get cached flow config by flow id
     *
     * @param string $id, flow id
     * @return mixed, array || NULL
     */
    public static function get_cached_config($id)
    {
        if (isset(self::$_cached_config[$id])) {
            return self::$_cached_config[$id];
        }
    }

    /**
     * set flow config loader
     * 
     * @param string $id, loader id
     * @param callback $loader, custom callback
     * @return NULL
     */
    public static function set_loader($id, $loader)
    {
        if (! $id || ! is_string($id) || ! $id) {
            throw new Flox_Exception("Loader id must be string");
        }

        if (! is_callable($loader)) {
            throw new Flox_Exception("Loader must be callable");
        }

        self::$_loader[$id] = $loader;
    }

    /**
     * load flow config by custom loader
     *
     * @param string $id, flow id
     */
    public static function load($id)
    {
        foreach (self::$_loader as $loader) {
            $config = $loader($id);
            if (is_array($config) && $config) {
                return $config;
            }
        }
    }

    /**
     * set decesion expr closure function
     *
     * @param string $id, closure id
     * @param callback $closure, closure
     */
    public static function set_closure($id, $closure)
    {
        self::$_closure[$id] = $closure;
    }

    public static function get_closure($id)
    {
        if (isset(self::$_closure[$id])) {
            return self::$_closure[$id];
        }
    }

    /**
     * construct
     *
     * @param string $id, flow id
     * @param mixed $config, flow config, array || NULL
     */
    public function __construct($id, $config)
    {
        if (! $id || ! is_string($id)) {
            throw new Flox_Exception("Failed to create flow by NULL id");
        }

        /**
         * try to load config from cache
         */
        if ($cached_config = Flox_Flow::get_cached_config($id)) {
            $this->_config = $config = $cached_config;
        }

        /**
         * try to load config by custom callback
         */
        if (! $config) {
            $config = Flox_Flow::load($id);
            if (! $config || ! is_array($config)) {
                throw new Flox_Exception("Failed to load flow config");
            }
        }

        if (! $config || ! is_array($config)) {
            throw new Flox_Exception("Flow config must be array");
        }

        /**
         * valid config is not from cache
         */
        if (! $cached_config) {
            /**
            array(
                'param' => array(   // flow param
                    array(
                        'name' => 'param1', // param name
                        'default' => '',    // param default value
                    ),
                ), 
                'entry' => 'entity_id', // flow entry entity id
                'entity' => array(      // flow entity array
                    'entity_id' => array(
                        'type' => 'process', // process or  decesion
                        'proto' => '',  // entity proto
                        'arg' => array()    // entity args
                        'decesion' => array( // decesions
                            'expr' => '',
                            'next' => '',
                        ),
                    ),
                ),
            )
             */

            if (! isset($config['param'])) {
                $config['param'] = array();
            }

            foreach ($config['param'] as $param) {
                if (! isset($param['name']) || ! is_string($param['name'])) {
                    throw new Flox_Exception("Flow param name must be string");
                }
            }

            if (! isset($config['entry']) || ! is_string($config['entry'])) {
                throw new Flox_Exception("Flow entry must be string");
            }

            if (! isset($config['entity']) || ! is_array($config['entity']) 
                || ! $config['entity']) {
                throw new Flox_Exception("Flow entity must not be empty");
            }

            foreach ($config['entity'] as $entity_id => $entity) {
                if (! $entity_id) {
                    throw new Flox_Exception("Entity id must be string");
                }

                if (! $entity || ! is_array($entity)) {
                    throw new Flox_Exception("Entity must be array");
                }

                if (! isset($entity['type']) 
                    || ! in_array($entity['type'], array('process', 'decesion'))
                ) {
                    throw new Flox_Exception("Entity type must be "
                        . "procee or decesion");
                }
                
                if (! isset($entity['decesion'])) {
                    $config['entity'][$entity_id]['decesion']
                        = $entity['decesion'] = array();
                }

                if ($entity['type'] == 'process') {
                    if (! isset($entity['proto']) || ! $entity['proto'] 
                        || ! is_string($entity['proto'])
                    ) {
                        throw new Flox_Exception("Entity proto must be string");
                    }

                    if (! isset($entity['arg'])) {
                        $config['entity'][$entity_id]['arg'] 
                        = $entity['arg'] = array();
                    }

                    if (count($entity['decesion']) > 1) {
                        throw new Flox_Exception("Entity proto only "
                            . "have one decesion");
                    }

                    if ($entity['decesion']) {
                        $decesion = $entity['decesion'][0];
                        if (
                            ! isset($decesion['next']) 
                            || ! is_string($decesion['next'])
                        ) {
                            throw new Flox_Exception("Decesion next must be " 
                                . "string");
                        }

                        if (! isset($config['entity'][$decesion['next']])) {
                            throw new Flox_Exception("Decesion next entity "
                                . "not found");
                        }
                    }

                    $proto = Flox_Proto::instance($entity['proto']);
                }
                
                if ($entity['type'] == 'decesion') {
                    if (! $entity['decesion']) {
                        throw new Flox_Exception("Entity must have a decesion");
                    }
                    
                    foreach ($entity['decesion'] as $decesion) {
                        if (! isset($decesion['expr']) 
                            || ! is_string($decesion['expr'])
                        ) {
                            throw new Flox_Exception("Decesion expr must be "
                                . "string");
                        }

                        $closure_id = md5($decesion['expr']);
                        $expr = "return ({$decesion['expr']});";
                        if (! Flox_Util::check_php_syntax($expr)) {
                            throw new Flox_Exception("Decesion expr is invalid "
                                . "php source code");
                        }
                        if (! $closure = Flox_Flow::get_closure($closure_id)) {
                            $closure = create_function('', $expr);
                            if (! $closure) {
                                throw new Flox_Exception("Failed to create "
                                    . "closure");
                            }
                            Flox_Flow::set_closure($closure_id, $closure);
                        }


                        if (! isset($decesion['next']) 
                            || ! is_string($decesion['next'])) {
                                throw new Flox_Exception("Decesion next must be "
                                    . "string");
                        }

                        if (! isset($config['entity'][$decesion['next']])) {
                            throw new Flox_Exception("Decesion next entity "
                                . "not found");
                        }
                    }
                }
            }

            if (! isset($config['entity'][$config['entry']])) {
                throw new Flox_Exception("Flow entry entity not exists");
            }

            $this->_config = $config;
            Flox_Flow::set_cached_config($id, $config);
        }

    }

    /**
     * execute flow
     *
     * @param array $arg 
     */
    public function execute($arg = array())
    {
        Flox::current()->set_current_flow($this);
        $entity_id = $this->_config['entry'];

        while ($entity_id && isset($this->_config['entity'][$entity_id])) {
            $entity_id = $this->_execute_entity($entity_id);
        }
    }

    /**
     * execute entity
     *
     * @param string $id, entity id
     */
    private function _execute_entity($id)
    {
        $entity = $this->_config['entity'][$id];

        if ($entity['type'] == 'process') {
            $proto = Flox_Proto::instance($entity['proto']);
            $proto->execute($entity['arg']);
        }

        foreach ($entity['decesion'] as $decesion) {
            if ($entity['type'] == 'process') {
                return $decesion['next'];
            } elseif ($entity['type'] == 'decesion') {
                $closure_id = md5($decesion['expr']);
                $closure = Flox_Flow::get_closure($closure_id);
                if ($closure()) {
                    return $decesion['next'];
                }
            }

        }
            

    }
}
