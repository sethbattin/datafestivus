<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/../notweb/data.php');

$name = '';
$call = '';
if (array_key_exists('name', $_POST)){
    $name = preg_replace('/[^\w]/i', '', $_POST['name']);
}
if (array_key_exists('call', $_POST)){
    $call = $_POST['call'];
}
$errors = [];
if (!$name) {
    $errors['name'] = "'name' parameter is required.";
}
$call_list = array('start');
if (!$call) {
    $errors['call'] = "'call' parameter is required.";
}
$status = 'success';
$rtcConnection = null;

function df_return()
{
    global $status, $errors, $rtcConnection;
    header("Content-Type: application/json");
    die(json_encode(array(
        'result' => $status,
        'errors' => $errors,
        'connection' => $rtcConnection
    )));
}

set_exception_handler(function(Exception $ex){
    global $status, $errors;
    http_response_code(500);
    $status = 'error';
    $errors['server'] = $ex->getMessage();
});
register_shutdown_function('df_return');


$store = \DataFestivus\get_store();
switch ($call) {
    case 'start':
        $offer = null;
        if (array_key_exists('connection', $_POST)) {
            $offer = json_decode($_POST['connection']);
        }
        if (!$offer) {
            $errors['connection'] =
                "'connection' parameter required for call 'start'.";
        } elseif (!property_exists($offer, 'offer')) {
            $errors['connection'] =
                "connection[offer] is required call 'start'.";
        } elseif ($exists = $store->getOffer($name)){
            $errors['connection'] = 
                sprintf("connection '%s' already exists.", $name);
        } else {
            $rtcConnection = $store->offerCreate($name, json_encode($offer->offer));
        }
        break;
    case 'update':
        throw new Exception('not implemented.');
    default:
        $errors['call'] = "'call' parameter must be one of [\"" . 
            implode('", "', $call_list) . '"].';
        break;
}

if (count($errors)) {
    $status = 'error';
    http_response_code(400);
}