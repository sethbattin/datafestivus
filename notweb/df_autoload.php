<?php

namespace DataFestivus;
use DataFestivus\RTCStore\RTCStoreInterface;

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
 * @return RTCStore\RTCStoreInterface
 * @throws \Exception if instance cannot be instantiated from 'rtc_store' config
 */
function get_store()
{
    global $df_config;
    // default store  
    $storeClass = '\DataFestivus\RTCStore\CSV';
    if ($sc = $df_config['rtc_store']) {
        $storeClass = $sc;
    }
    if (!class_exists($storeClass)) {
        throw new \Exception(sprintf("invalid RTC store class name config '%s'.", $storeClass));
    }
    $RTCstore = new $storeClass($df_config);
    if (!($RTCstore instanceof RTCStoreInterface)) {
        throw new \Exception("RTC store class name is not a valid implementation.");
    }
    return $RTCstore;
}


