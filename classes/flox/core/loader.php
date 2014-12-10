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
        return call_user_func_array(self::$_loader[$type], $config);
    }
}
