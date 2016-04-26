<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 2/1/16
 * Time: 7:42 PM
 */

namespace DataFestivus\RTCStore;


use DataFestivus\RTCStore\Adapter\AdapterInterface;

class RTCStore 
{
    public static function instance()
    {
        if (!array_key_exists('df_config', $GLOBALS)){
            throw new \Exception("no storage config available.");
        }        
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
    
    private $adapater;
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapater = $adapter;
    }

    /**
     * Create an RTC offer instance and save it.
     * @param string $name
     * @param string $offer RTCOffer json
     * @return \DataFestivus\RTCStore\Connection
     */
    public function offerCreate($name, $offer)
    {
        $connection = new Connection();
        $connection->setName($name);
        $connection->setOffer($offer);
        $this->save($connection);
        return $connection;
    }

    /**
     * Answer an existing offer and save it.
     * @param Connection $connection
     * @param string $answer RTCOfferAnswer json
     * @return void
     */
    public function offerAnswer(Connection $connection, $answer)
    {
        $jsonObj = json_decode($answer);
        if ($jsonObj && // valid json
            $answer != $connection->getAnswer()
        ) {
            $connection->setAnswer($answer);
            $this->save($connection);
        }
    }


    /**
     * @param Connection $connection
     * @param string $candidate - RTCIceCandidate json
     * @return void
     */
    public function addIceCandidate(Connection $connection, $candidate)
    {
        $connection->addCandidate($candidate);
        $this->save($connection);
    }
    /**
     * Retrieve an RTC offer with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Connection|null
     */
    public function getOffer($name)
    {
        return $this->getAdapter()->getOffer($name);
    }
    

    /* @return AdapterInterface */
    private function getAdapter()
    {
        return $this->adapater;
    }
    private function save(Connection $connection)
    {
        $this->getAdapter()->save($connection);
    }

}