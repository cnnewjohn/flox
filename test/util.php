<?php
include "autoload.php";

$s = '<?php echo 1;';
$r = Flox_Core_Util::check_php_syntax($s);
var_dump($r);
