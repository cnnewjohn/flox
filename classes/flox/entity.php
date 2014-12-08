<?php
/**
 * entity.php 流程节点
 */

class Flow_Entity
{
    private $_entity_id;
    private $_entity;

    public static function factory($entity_id, $entity_config)
    {

    }

    public function __construct($entity_id, $entity_config)
    {
        $entity_config = $entity_config + array(
            'id' => '',             // 节点id
            'type' => '',           // 节点类型,process处理节点, device决策节点
            'proto_id' => '',       // 操作原型id
            'proto_arg' => array(), // 操作传参
            'device' => array(),    // 决策器数组

        );

        array(
            0 => array(
                'entity_id' => '',  // 后继节点id
                // 决策器模式类型
                // direct，直接跳转，用在process中
                // build,构造模式，根据用户提交参数重新构造表达式
                // expr，表达式，直接执行表达式
                'type' => '',
                'build' => '',
                'expr' => '',
            ),
            1 => array(),
        );

        $this->_entity_id = $entity_id;
        $this->_entity_config = $entity_config;
    }

    public function execute()
    {
        /**
         * 如果当前是处理节点，则需要执行操作
         */
        if ($this_entity_config['type'] == 'process') {
            Flox_Proto::factory();
        }

        $next_entity_id = NULL;

        /**
         * 进入决策
         */
        foreach ($this->_entity_config['device'] as $idx => $device) {
            if ($device['type'] == 'direct') {
                $next_entity_id = $device['entity_id'];
                break;
            }

            $closure_id = NULL;
            $expr = ''

            if ($device['type'] == 'expr') {
            }

            if ($device['type'] == 'build') {
            }

            if ($expr) {
                /**
                 * 根据表达式特征创建闭包，以避免反复创造导致内存泄漏
                 */
                $closure_id = md5($expr);
                if (! $closure = Flox::current()->get_closure($closure_id)) {
                    if (! $closure = create_function('$_FLOX', $expr)) {
                        // throw exception
                    }
                    Flox::current()->set_closure($closure_id, $closure);

                    /**
                     * 执行闭包判断
                     */
                    $flag = call_user_func_array($closure, Flox::current());
                    if ($flag) {
                        $next_entity_id = $device['entity_id'];
                    }
                }
            }

        }

        return $next_entity_id;
        
    }
}
