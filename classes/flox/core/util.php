<?php
/**
 * Flox Util Class
 */
class Flox_Core_Util
{
    private static $_closure = array();

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
    public static function check_php_syntax($code, & $err = '')
    {
        $des = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('file', '/dev/null', 'a')
        );
        $proc = proc_open('/usr/local/php/bin/php -l ', $des, $pipe);
        fwrite($pipe[0], $code);
        fclose($pipe[0]);

        $out = stream_get_contents($pipe[1]);
        fclose($pipe[1]);

        proc_close($proc);

        if (strpos($out, 'Errors parsing')) {
            $err = $out;
            return FALSE;
        }

        return TRUE;
    }

    /**
     * return value of value path
     *
     * @param mixed $value
     * @param string $path
     * @return mixed
     *
     */
    public static function path($value, $path = '', $default = NULL)
    {
        if ($path === '' || $path === NULL) {
            return $value;
        }

        if (! is_array($value)) {
            return $default;
        }

        $pathes = explode('.', $path);
        $key = $pathes[0];
        array_shift($pathes);
        $path = implode('.', $pathes);
        $value = isset($value[$key])? $value[$key] : $default;
        
        return self::path($value, $path, $default);
    }

    public static function create_closure($param, $expr, $id = NULL)
    {
        if ($id === NULL) {
            $id = "{$param}\0{$expr}";
        }

        if (! isset(self::$_closure[$id])) {
            self::$_closure[$id] = create_function($param, $expr);
        }
        return self::$_closure[$id];
    }
}
