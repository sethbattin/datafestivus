<?php
include_once(__DIR__ . '/../notweb/df_autoload.php');

$store = \DataFestivus\RTCStore\RTCStore::instance();
$name = '*'; // doesn't work
if (isset($_GET['name'])){
    $name = $_GET['name'];
}
$connections = $store->getOffer($name);
var_dump($connections);