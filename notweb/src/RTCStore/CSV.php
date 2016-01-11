<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 1/2/16
 * Time: 6:57 PM
 */

namespace DataFestivus\RTCStore;

/**
 * Class CSV 
 * Basic storage for RTC candidates.  No DB required and easy to debug.
 * @package DataFestivus\RTCStore
 */
class CSV implements RTCStoreInterface
{
    private $storePath;
    private function getStorePath(){
        return $this->storePath;
    }
    
    public function __construct(array $config)
    {
        if (!array_key_exists('csv_store', $config) ||
            !array_key_exists('file_path', $config['csv_store'])
        ){
            throw new \Exception("invalid configuration data.");
        }
        $this->storePath = $config['csv_store']['file_path'];
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
     * Retrieve an RTC offer with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Connection|null 
     */
    public function getOffer($name)
    {
        $connections = $this->getAllConnections();
        $result = null;
        if (array_key_exists($name, $connections)){
            $result = $connections[$name];
        }
        return $result;
    }
    
    private function getAllConnections(){
        $fh = fopen($this->getStorePath(), 'r');
        if (!$fh){
            throw new \Exception("Could not open rtcstore csv file.");
        }
        $connections = [];
        while ($fh && (FALSE !== ($row = fgetcsv($fh)))){
            if (count($row) == 4){
                $connection = new Connection();
                $connection->setName($row[0]);
                $connection->setOffer($row[1]);
                $connection->setAnswer($row[2]);
                $connection->setCandidates(unserialize($row[3]));
                $connections[$row[0]] = $connection;
            }
        }
        fclose($fh);
        return $connections;
    }
    
    private function save(Connection $connection)
    {
        $connections = $this->getAllConnections();
        $connections[$connection->getName()] = $connection;
        $this->saveAllConnections($connections);
        
    }

    /**
     * @param Connection[] $connections
     * @throws \Exception
     */
    private function saveAllConnections(array $connections)
    {
        $fh = fopen($this->getStorePath(), 'w');
        if (!$fh){
            throw new \Exception("Could not open rtcstore csv file.");
        }
        foreach ($connections as $connection) {
            $row = [
                $connection->getName(),
                $connection->getOffer(),
                $connection->getAnswer(),
                serialize($connection->getCandidates())
            ];
            fputcsv($fh, $row);
        }
        fclose($fh);
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
}