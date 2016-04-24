<?php

namespace DataFestivus;
use DataFestivus\RTCStore\RTCStore;
use DataFestivus\RTCStore\Adapter\AdapterInterface;

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

/**
 * @global $df_config
 * @return RTCStore
 * @throws \Exception if instance cannot be instantiated from 'rtc_store' config
 */
function get_store()
{
    global $df_config;
    // default store  
    $adapterClass = '\DataFestivus\RTCStore\Adapter\CSV';
    if ($sc = $df_config['rtc_store']) {
        $adapterClass = $sc;
    }
    if (!class_exists($adapterClass)) {
        throw new \Exception(sprintf("invalid RTC store class name config '%s'.", $adapterClass));
    }
    $adapter = new $adapterClass($df_config);
    if (!($adapter instanceof AdapterInterface)) {
        throw new \Exception("RTC store class name is not a valid implementation.");
    }
    $RTCstore = new RTCStore($adapter);
    return $RTCstore;
}


