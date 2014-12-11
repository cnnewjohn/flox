<?php
/**
 * Flox Util Class
 */
class Flox_Core_Util
{
    /**
     * create uniq id by time
     *
     * @param string $pre
     * @param string $suf
     */
    public static function uniq_id($pre = '', $suf = '')
    {
        list($usec, $sec) = explode(' ', microtime());
        $usec = intval($usec * pow(10, 8));
        return $pre . date('YmdHis'). "_".$usec . $suf;
    }


    /**
     * check php syntax
     *
     * @param string $code, php source code
     */
    public static function check_php_syntax($code)
    {
        return TRUE;
    }
}
