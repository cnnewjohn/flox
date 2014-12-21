<?php

error_reporting(-1);
function autoload($class_name) {
    $class_file = strtolower($class_name);
    $class_file = str_replace("_", "/", $class_file);
    $class_file = dirname(__FILE__) . "/../classes/" . $class_file . ".php";
    include($class_file);
}

spl_autoload_register('autoload');
