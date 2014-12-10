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
        return new self($config);
    }

    public function __construct($config)
    {
        $this->config($config);
    }

    public function get_config()
    {
        return $this->_config;
    }

    public function set_config($config)
    {
        if (is_string($config)) {
            $config = Flox_Loader::load('flow', $config);
        }

        if (is_array($config)) {
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
    public function execute()
    {
        $entity_id = $this->_flow_config['entry'];

        while ($entity_id) {
            $entity_config = $this->_flow_config['entity'][$entity_id];
            $entity = Flox_Entity::factory($entity_config);
            $entity_id = $entity->execute();
        }
    }
}
