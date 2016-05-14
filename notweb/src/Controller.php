<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 4/23/16
 * Time: 10:02 PM
 */

namespace DataFestivus;


use DataFestivus\RTCStore\RTCStore;

class Controller {

    const CALL_OFFER = 'offer';   // save a new connection's offer with 'name' identifier
    const CALL_ANSWER = 'answer';   // answer a connection with 'name' identifier
    const CALL_FETCH = 'fetch';   // get data for a connection by 'name' 
    
    public static function getCalls() {
        return [
            self::CALL_OFFER,
            self::CALL_ANSWER,
            self::CALL_FETCH,
        ];
    }

    /**
     * @param array $args - request arguments, i.e. $_POST
     * @param RTCStore $store
     * @return array [
     *  int $statusCode, 
     *  string $statusMsg, 
     *  RTCConnection $connection, 
     *  string[] $errors
     * ]
     * @throws \Exception
     */
    public static function signal(array $args, RTCStore $store = null)
    {
        $call = '';
        $name = '';
        $connection = '';
        extract($args, EXTR_IF_EXISTS);
        
        if ($store == null){
            $store = RTCStore::instance();
        }
        
        $signal = new Controller($store);
        
        switch ($call) {
            case self::CALL_OFFER:
                $signal->offer($name, $connection);
                break;
            case self::CALL_ANSWER:
                $signal->answer($name, $connection);
                break;
            case self::CALL_FETCH:
                $signal->fetch($name);
                break;
            default:
                $signal->error('invalid call', [
                    'call' =>"'call' parameter must be one of [\"" .
                        implode('", "', self::getCalls()) . '"].']);
                break;
        }
        
        return $signal->getCallResult();
    }
    
    private $rtcStore = null;
    private function getStore()
    {
        return $this->rtcStore;
    }
    
    private $errors = [];
    private $code = 200;
    private $status = 'success';
    private $rtcConnection = null;
    
    public function __construct(RTCStore $rtcStore)
    {
        $this->rtcStore = $rtcStore;
    }
    
    /**
     * @return array [
     *  int $statusCode,
     *  string $statusMsg,
     *  RTCConnection $connection,
     *  string[] $errors
     * ]
     */
    public function getCallResult()
    {
        return [
            $this->code,
            $this->status,
            $this->rtcConnection,
            $this->errors
        ];
    }

    public static function errorResult()
    {
        return [500, 'no storage', null, []];
    }
    
    public function offer($name, $connection)
    {
        $offer = json_decode($connection);
    
        if (!$offer) {
            $this->error('invalid', ['connection' =>
            "'connection' parameter required for call 'start'."]);
        } else if (!property_exists($offer, 'offer')) {
            $this->error('invalid', ['connection' =>
                "connection[offer] is required call 'start'."]);
        } else if ($exists = $this->getStore()->getOffer($name)){
            $this->error('invalid', ['connection' =>
                sprintf("connection '%s' already exists.", $name)]);
        } else {
            $this->rtcConnection = $this->getStore()
                ->offerCreate($name, json_encode($offer->offer));

            if (property_exists($offer, 'candidates')){
                $candidates = (array)$offer->candidates;
                foreach ($candidates as $id => $candidate){
                    $this->getStore()->addIceCandidate(
                        $this->rtcConnection, 
                        json_encode($candidate)
                    );
                }
            }
        }
        
    }
    
    public function answer($name, $connection)
    {
        $update = null;
        if (!$connection) {
            $this->error('invalid', ['connection' =>
                "'connection' parameter required for call 'update'."]);
        } else if (!$update = json_decode($connection, true)){
            $this->error('invalid', ['connection' =>
                "'connection' parameter invalid."]);
        } else if (!$this->rtcConnection = $this->getStore()->getOffer($name)){
            $this->error('notfound', 
                ['name' => sprintf("connection '%s' not found.", $name)]);
            $this->code = 404;
        } else {
            if (array_key_exists('answer', $update) &&
                $update['answer']
            ){
                $this->getStore()->offerAnswer($this->rtcConnection, json_encode($update['answer']));
            }
            if (array_key_exists('candidates', $update) &&
                $update['candidates']
            ){
                $candidates = $update['candidates'];
                foreach ($candidates as $id => $candidate){
                    $this->getStore()->addIceCandidate($this->rtcConnection, json_encode($candidate));
                }
            }
        }
    }
    
    public function fetch($name)
    {
        $this->rtcConnection = $this->getStore()->getOffer($name);
        if (!$this->rtcConnection){
            $this->error('not found', ['name' => sprintf("connection '%s' not found.", $name)]);
            $this->code = 404;
        }
    }
    
    public function error($message, array $errors = [])
    {
        $this->code = 400;
        $this->status = $message;
        foreach ($errors as $e_name => $error){
            $this->errors[$e_name] = $error;
        }
    }
    
    
}