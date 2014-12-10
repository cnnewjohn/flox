<?php

class Flox_Core_Flow
{
    /**
     * 当前流程配置
     */
    private $_config = array();

    /**
     * 流程的入口原型
     */
    private $_entry_proto;

    public static function factory($config)
    {
        Flox::profile("Flow::factory, " . print_r($config, TRUE), __FILE__, __LINE__);
        return new self($config);
    }

    public function __construct($config)
    {
        $this->set_config($config);
    }

    public function get_config()
    {
        return $this->_config;
    }

    public function id()
    {
        return $this->_config['id'];
    }

    public function set_config($config)
    {
        if (is_string($config)) {
            Flox::profile("Flow config is string");
            $config = Flox_Loader::load('flow', $config);
        }

        if (is_array($config)) {
            Flox::profile("Flow config is array");
            $this->_config = $config;
        }

        if ($config instanceof $this) {
            $config = $config->get_config;
        }

        $this->_config = $this->_config + array(
            'id' => Flox_Util::uniq_id("FLOW_AUTO_"), 
            'entry' => NULL,    // 入口节点
            'entity' => array(),    // 执行节点集合
        );

        Flox::profile("set Flow config, ". print_r($this->_config, TRUE), __FILE__, __LINE__);

    }

    /**
     * 获取当前流程的入口原型
     */
    public function proto()
    {
        return $this->_entry_proto;
    }

    /**
     * 执行当前流程
     */
    public function execute($arg = array())
    {
        Flox::profile("Flow::execute, ". print_r($arg, TRUE), __FILE__, __LINE__);

        Flox::current()->set_current_flow($this);
        $entity_id = $this->_config['entry'];

        Flox::profile("Flow entry id, ".print_r($entity_id, TRUE), __FILE__, __LINE__);

        while (isset($this->_config['entity'][$entity_id])) {
            $entity_config = $this->_config['entity'][$entity_id];
            Flox::profile("Flow Execute Entity: ".print_r($entity_id, TRUE)." ". print_r($entity_config, TRUE), __FILE__, __LINE__);
            $entity = Flox_Entity::factory($entity_config);
            $entity_id = $entity->execute();
        }

        Flox::profile("Flow ".$this->_config['id']." Execute complete", __FILE__, __LINE__);
    }
}
