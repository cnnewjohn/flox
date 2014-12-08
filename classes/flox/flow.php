<?php
/**
 * flow.php
 */

class Flox_Flow
{
    /**
     * 当前流程id和配置
     */
    private $_flow_id;
    private $_flow_config = array();

    /**
     * 当前正在执行的节点
     */
    private $_current_entity_id = NULL;
    private $_current_entity;

    /**
     * 各节点执行次数及耗时统计
     */
    private $_entity_execute_times = array();
    private $_entity_execute_time = array();



    /**
     * 工厂方法，用来加载一个流程实例
     */
    public static function factory($flow_id, $flow_config)
    {
    }

    /**
     * 构造方法,初始化
     */
    public function __construct($flow_id, $flow_config)
    {
        /**
         * 流程配置初始化
         */
        $flow_config = $flow_config + array(
            'id' => '',             // 流程id
            'entry' => '',          // 入口节点id
            'entity' => array(),    // 节点数组
        );

        $this->_flow_id = $flow_id;
        $this->_flow_config = $flow_config;

        foreach ($this->_flow_config['entity'] as $entity) {
            $entity_id = $entity['id'];
            $this->_entity_execute_times[$entity_id] = 0;
            $this->_entity_execute_time[$entity_id] = 0;
        }

    }

    public function get_current_entity()
    {
        return $this->_current_entity;
    }

    public function get_current_entity_id()
    {
        return $this->_current_entity_id;
    }

    /**
     * 执行流程
     */
    public function execute()
    {
        /**
         * 通知执行引擎，进入当前流程
         */
        Flox::current()->enter_flow($this->_flow_id);
        Flox::current()->set_current_flow($this);

        $entity_id = $this->_flow_config['entry'];

        while ($entity_id) {
            
            $entity_config = $this->_flow_config['entity'][$entity_id];
            $entity = Flow_Entity::factory($entity_id, $entity_config);

            $this->_current_entity = $entity;
            $this->_current_entity_id = $entity_id;

            /**
             * 累加当前节点执行次数
             */
            $this->_entity_execute_times[$entity_id] ++;

            /**
             * 执行当前节点并计算耗时
             */
            $time_enter_entity = microtime(TRUE);
            $next_entity_id = $entity->execute();
            $this->_entity_execute_time[$entity_id] += 
                (microtime(TRUE) - $time_enter_entity);

            $entity_id = $next_entity_id;
        }

        /**
         * 通知执行引擎，退出当前流程
         */
        Flox::current()->exit_flow();
    }
}
