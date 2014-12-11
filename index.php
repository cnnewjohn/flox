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

$proto_id = 'proto_1';
$proto_config = array(
    'expr' => 'echo $str;',
    'param' => array(
        array(
            'name' => 'str',
        ),
    ),
);
Flox_Proto::instance($proto_id, $proto_config);


$flow_id = 'flow_1';
$flow_config = array(
    'entity' => array(
        1 => array(
            'type' => 'process',    
            'proto' => $proto_id,
            'arg' => array(
                'str' => "{{VAR.str}}\r\n",
            ),
            'decesion' => array(
                array(
                    'next' => '2',
                )
            )
        ),
        2 => array(
            'type' => 'decesion',
            'decesion' => array(
                array(
                    'expr' => ' 0',
                    'next' => '1',
                ),
            ),
        ),
        
        
    ),
    'entry' => '1',
    'param' => array(
        array(
            'name' => 'str',
        ),
    ),
);
Flox_Flow::factory($flow_id, $flow_config);

function profile($msg, $file, $line) {
    //echo date('Y-m-d H:i:s')."\t{$file}\t{$line}\t{$msg}\r\n";
}
Flox::set_profile('profile');
$flox = Flox::factory($flow_id);
$flox->execute(array('hehe'));
