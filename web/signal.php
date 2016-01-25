<?php
/**
 * Check and fetch valid parameters from POST parameters.
 * For valid values of 'call' perform that action.
 * For errors, populate the error value.
 * Return a standard json structure after all calls, errors, and exceptions via
 *   shutdown_- and exception_- handlers
 *
 * Valid calls are defined by:  $CALL_LIST.
 * 
 * 'fetch' - fetch a connection by 'name'
 * 
 */
const CALL_START = 'start';   // create and save a connection with 'name' identifier
const CALL_FETCH = 'fetch';   // get data for a connection by 'name'
const CALL_UPDATE = 'update'; // add data to an existing connection  

include_once($_SERVER['DOCUMENT_ROOT'] . '/../notweb/data.php');

$CALL_LIST = [CALL_START, CALL_FETCH, CALL_UPDATE];
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
if (!$call) {
    $errors['call'] = "'call' parameter is required.";
}
$status = 'success';
$statusCode = 200;
$rtcConnection = null;

function df_return()
{
    global $status, $statusCode, $errors, $rtcConnection;
    header("Content-Type: application/json");

    http_response_code($statusCode);
    die(json_encode(array(
        'result' => $status,
        'errors' => $errors,
        'connection' => $rtcConnection
    )));
}

set_exception_handler(function(Exception $ex){
    global $status, $statusCode, $errors;
    $statusCode = 500;
    $status = 'error';
    $errors['server'] = $ex->getMessage();
});
register_shutdown_function('df_return');


$store = \DataFestivus\get_store();
switch ($call) {
    case CALL_START:
        $offer = null;
        if (array_key_exists('connection', $_POST)) {
            $offer = json_decode($_POST['connection']);
        }
        if (!$offer) {
            $errors['connection'] =
                "'connection' parameter required for call 'start'.";
        } else if (!property_exists($offer, 'offer')) {
            $errors['connection'] =
                "connection[offer] is required call 'start'.";
        } else if ($exists = $store->getOffer($name)){
            $errors['connection'] = 
                sprintf("connection '%s' already exists.", $name);
        } else {
            $rtcConnection = $store->offerCreate($name, json_encode($offer->offer));
        }
        break;
    case CALL_FETCH:
        $rtcConnection = $store->getOffer($name);
        if (!$rtcConnection){
            $statusCode = 404;
            $status = 'notfound';
            $errors['name'] = sprintf("connection '%s' not found.", $name);
        } else {
            // return $rtcConnection 
        }
        break;
    case CALL_UPDATE:
        $update = null;
        $rtcConnection = $store->getOffer($name);
        if (array_key_exists('connection', $_POST)) {
            $update = json_decode($_POST['connection'], true);
        }
        if (!$update){
            $errors['connection'] =
                "'connection' parameter required for call 'update'.";
        } else if (!$rtcConnection){
            $statusCode = 404;
            $status = 'notfound';
            $errors['name'] = sprintf("connection '%s' not found.", $name);
        } else {
            if (array_key_exists('answer', $update) &&
                $update['answer']
            ){
                $store->offerAnswer($rtcConnection, json_encode($update['answer']));
            }
            if (array_key_exists('candidates', $update) &&
                $update['candidates']
            ){
                $candidates = $update['candidates'];
                foreach ($candidates as $id => $candidate){
                    $store->addIceCandidate($rtcConnection, json_encode($candidate));
                }
            }
        }
        break;
    default:
        $errors['call'] = "'call' parameter must be one of [\"" . 
            implode('", "', $CALL_LIST) . '"].';
        break;
}

if (count($errors)) {
    if ($statusCode == 200){
        $statusCode = 400;
    }
    if ($status == 'success') {
        $status = 'error';
    }
}