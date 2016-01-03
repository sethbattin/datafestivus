<?php

namespace DataFestivus;
use DataFestivus\RTCStore\RTCStoreInterface;

include_once('autoload.php');

$df_config = require('.config.php');

/**
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


