<?php

namespace DataFestivus;

function autoload ($classname){
    $name_parts = explode("\\", $classname );
    if (!($ns = array_shift($name_parts)) ||
        ! count($name_parts) ||
        !($ns == __NAMESPACE__)
    ){
        return;
    }
    array_unshift($name_parts, __DIR__ . '/src/');
    $file = implode(DIRECTORY_SEPARATOR, $name_parts) . '.php';
    if (file_exists($file)){
        include_once($file);
    }
}
spl_autoload_register('\DataFestivus\autoload');

$df_config = require('.config.php');


