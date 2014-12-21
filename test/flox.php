<?php
include "autoload.php";


$apis = array(
    'closure' => array(
        'in' => array(
            array('name' => 'code')
        ),
        'out' => array(
            array('name' => 'return'),
        ),
        'expr' => 'return eval($code);',
    ),

);

foreach ($apis as $api_id => $api) {
    Flox_Api::instance($api_id, $api);
}

$flow_config = array(
    'node' => array(
        'node1' => array(
            'api' => 'closure',
            'arg' => array('code' => 'return time();'),
            'bind' => array(
                array('source' => 'return', 'target' => 'c')
            ),
            'direction' => array(
                array('next' => 'node1', 'expr' => 1),
            ),
        ),
    ),
    'entry' => 'node1',

);
$flox = Flox::factory('flow1', $flow_config);
$flox->execute();
//$flow = Flox_Flow::$current;
//var_dump($flow->context);
