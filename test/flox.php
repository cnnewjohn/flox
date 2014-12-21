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
    'flow' => array(
        'in' => array(
            array('name' => 'flow_id'),
            array('name' => 'arg',),
        ),
        'out' => array(),
        'expr' => 'return Flox_Flow::factory($flow_id)->execute($arg);',
    ),

);

foreach ($apis as $api_id => $api) {
    Flox_Api::instance($api_id, $api);
}

$flow_config = array(
    'node' => array(
        'node1' => array(
            'api' => 'closure',
            'arg' => array('code' => 'var_dump("Context", $CONTEXT);'),
            'bind' => array(
                array('source' => 'return', 'target' => 'c')
            ),
            'direction' => array(
            ),
        ),
    ),
    'entry' => 'node1',
    'in' => array(
        array('name' => 'id')
    ),
    'out' => array(
        array('name' => 'id')
    )

);
Flox_Flow::factory('flow1', $flow_config);
$flow_config2 = array(
    'node' => array(
        'node1' => array(
            'api' => 'flow',
            'arg' => array('flow_id' => 'flow1', 'arg' => array('id' => 1)),
            'bind' => array(),
        ),
    ),
    'entry' => 'node1',
    'in' => array(
        array('name' => 'id'),
    ),

);
$flox = Flox::factory('flow2', $flow_config2);
$flox->execute(array('id' => 10));
//$flow = Flox_Flow::$current;
//var_dump($flow->context);
