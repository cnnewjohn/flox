<?php

class Flox_Core_Loader
{
    private static $_loader = array();

    /**
     * 设置加载器
     *
     * @param string $type, 加载器类型，flow 流程, entity 节点, proto 原型
     */
    public static function set_loader($type, $loader)
    {
        self::$_loader[$type] = $loader;
    }


    /**
     * 加载
     *
     * @param string $type
     * @param string $id
     */
    public static function load($type, $id)
    {
        Flox::profile("Loader::load, {$type}, {$id}, Entity_id: ".Flox::current()->get_current_entity()->id()." , Flow_id: ".Flox::current()->get_current_flow()->id(), __FILE__, __LINE__);

        if (! isset(self::$_loader[$type])) {
            Flox::profile("loader {$type} not found", __FILE__, __LINE__);
            return;
        }
        return call_user_func_array(self::$_loader[$type], $config);
    }
}
