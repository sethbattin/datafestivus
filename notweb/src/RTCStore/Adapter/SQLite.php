<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 2/1/16
 * Time: 7:18 PM
 */

namespace DataFestivus\RTCStore\Adapter;

use DataFestivus\RTCStore\Connection;


class SQLite implements AdapterInterface
{
    private static $db;
    
    private static function makeDb($filename)
    {
        
        if (!is_readable($filename)){
            if (!touch($filename)) {
                throw new \Exception("Invalid DB configuration.");
            }
        }
        $dsn = 'sqlite:' . $filename;
        return new \PDO($dsn);
    }

    /**
     * @return \PDO
     * @throws \Exception
     */
    private function getDb()
    {
        
        
        if (is_null(self::$db)){
            self::$db = self::makeDb($this->file);
        }
                
        $create = <<<EOT
CREATE TABLE IF NOT EXISTS connections (    
id      INTEGER PRIMARY KEY,
name    TEXT    NOT NULL,
offer   TEXT,
answer  TEXT,
candidates TEXT)
EOT;

        self::$db->query($create);
        return self::$db;
    }
    
    private $file;
    
    public function __construct($dbconfig)
    {
        $this->file = $dbconfig['sqlite']['file_path'];
    }

    /**
     * Retrieve an RTC connection with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Connection
     */
    public function getOffer($name)
    {
        $select = $this->selectExecute($name);
        $row = $select->fetch(\PDO::FETCH_ASSOC);
        $connection = null;
        if ($row) {
            $connection = new Connection();
            $connection->setName($row['name']);
            $connection->setOffer($row['offer']);
            $connection->setAnswer($row['answer']);
            $connection->setCandidates(unserialize($row['candidates']));
        }
        return $connection;
    }

    public function save(Connection $connection)
    {
        $select = $this->selectExecute($connection->getName());
        $id = $select->fetchColumn(0);
        $params = array();
        if ($id){
            $params['id'] = $id;
            $statement = $this->getUpdate();            
        } else {
            $statement = $this->getInsert();
        }
        $params['name'] = $connection->getName();
        $params['offer'] = $connection->getOffer();
        $params['answer'] = $connection->getAnswer();
        $params['candidates'] = serialize($connection->getCandidates());
        
        $result = $statement->execute($params);
        if (!$result){
            throw new \Exception("failed to save connection record.");
        }
    }
    
    private function selectExecute($name)
    {
        $select = $this->getDb()->prepare(
            "SELECT * FROM connections WHERE name = :name");
        $select->execute(array('name' => $name));
        return $select;
    }
    private function getUpdate()
    {
        $sql = <<<EOT
UPDATE connections
SET
name = :name,
offer = :offer,
answer = :answer,
candidates = :candidates
WHERE id = :id
EOT;
        $update = $this->getDb()->prepare($sql);
        return $update;
    }
    private function getInsert()
    {
        $sql = <<<EOT
INSERT INTO connections (name, offer, answer, candidates)
VALUES (:name, :offer, :answer, :candidates)
EOT;
        $insert = $this->getDb()->prepare($sql);
        return $insert;
    }
}