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
     * @param $name
     * @param $candidate
     * @param $content
     * @return \DataFestivus\RTCStore\Connection
     */
    public function offerCreate($name, $candidate, $content)
    {
        $connection = new Connection();
        $connection->setName($name);
        $connection->setOffer($content);
        $this->save($connection);
    }

    /**
     * Answer an existing offer and save it.
     * @param Connection $connection
     * @param string $answer
     * @param string $candidate
     * @return void
     */
    public function offerAnswer(Connection $connection, $answer, $candidate)
    {
        $connection->setAnswer($answer);
        $connection->setCandidate($candidate);
        // TODO: Implement offerAnswer() method.
    }

    /**
     * Retrieve an RTC offer with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Connection|null 
     */
    public function getOffer($name)
    {
        $fh = fopen($this->getStorePath(), 'r');
        $connection = null;
        while (FALSE !== ($row = fgetcsv($fh))){
            if ((count($row) == 4) && $row[0] == $name){
                $connection = new Connection();
                $connection->setName($row[0]);
                $connection->setOffer($row[1]);
                $connection->setAnswer($row[2]);
                $connection->setCandidate($row[3]);
                break;
            }
        }
        return $connection;
    }
    
    private function save(Connection $connection)
    {
        $fh = fopen($this->getStorePath(), 'a');
        $row = [
            $connection->getName(),
            $connection->getOffer(),
            $connection->getAnswer(),
            $connection->getCandidate()
        ];
        fputcsv($fh, $row);
        fclose($fh);
    }
}