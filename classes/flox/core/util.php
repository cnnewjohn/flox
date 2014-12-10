<?php

class Flox_Core_Util
{
    /**
     * 生成唯一ID，默认
     *
     * @param string $pre, 前缀
     * @param string $suf, 后缀
     */
    public static function uniq_id($pre = '', $suf = '')
    {
        list($usec, $sec) = explode(' ', microtime());
        return $pre . date('YmdHis') . $suf;
    }
}
