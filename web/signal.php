<?php
$df_autoload = '/../notweb/df_autoload.php';
if (isset($_SERVER['df_autoload'])){
    $df_autoload = $_SERVER['df_autoload'];
}
include_once(__DIR__ . '/' . $df_autoload);

$errors = [];
$status = 'success';
$statusCode = 200;
$rtcConnection = null;

function df_return()
{
    global $status, $statusCode, $errors, $rtcConnection;
    header("Content-Type: application/json");

    http_response_code($statusCode);
    echo json_encode(array(
        'result' => $status,
        'errors' => $errors,
        'connection' => $rtcConnection
    ));
    exit();
}

set_exception_handler(function(Exception $ex){
    global $status, $statusCode, $errors;
    $statusCode = 500;
    $status = 'error';
    $errors['server'] = $ex->getMessage();
});
register_shutdown_function('df_return');

$store = \DataFestivus\RTCStore\RTCStore::instance();

list($statusCode, $status, $rtcConnection) = 
    \DataFestivus\Controller::signal($_POST, $store);
exit();