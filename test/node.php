<?php
include "autoload.php";

$api_id = 'httpget';
$api = Flox_Api::instance($api_id);
$api->in(array(
    'name' => 'url',
))->out(array(
    'name' => 'content',
    'path' => '',
))->expr('return ($url);');

$node = Flox_Node::instance(1, 'node');
$node->api($api_id)
    ->arg(array(
        'url' => 'http://www.baidu.com/',
    ))
    ->bind(array(
        'source' => 'content',
        'target' => 'http_content',
    ));
$ret = $node->execute();
var_dump($ret);
