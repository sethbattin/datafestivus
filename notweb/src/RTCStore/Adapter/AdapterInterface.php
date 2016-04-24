<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 1/2/16
 * Time: 6:58 PM
 */
namespace DataFestivus\RTCStore\Adapter;

use DataFestivus\RTCStore\Connection;

interface AdapterInterface {
    
    public function save(Connection $connection);

    /**
     * Retrieve an RTC connection with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Connection
     */
    public function getOffer($name);
}