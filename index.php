<?php
/**
 * index.php
 */


function autoload($class_name) {
    $class_file = strtolower($class_name);
    $class_file = str_replace("_", "/", $class_file);
    $class_file = dirname(__FILE__) . "/classes/" . $class_file . ".php";
    include($class_file);
}

spl_autoload_register('autoload');

$config = array(
    'id' => 'test_flow',
    'entity' => array(
        0 => array(
            'type' => 'process',    
        ),
    ),
    'entry' => '0',
);

function profile($msg, $file, $line) {
    echo date('Y-m-d H:i:s')."\t{$file}\t{$line}\t{$msg}\r\n";
}
Flox::set_profile('profile');
$flox = Flox::factory($config);
$flox->execute();
