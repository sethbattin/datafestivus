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
     * @return \DataFestivus\RTCStore\Offer
     */
    public function offerCreate($name, $candidate, $content)
    {
        $offer = new Offer();
        $offer->setName($name);
        $offer->setCandidate($candidate);
        $offer->setContent($content);
        $this->save($offer);
    }

    /**
     * Answer an existing offer and save it.
     * @param Offer $offer
     * @param $answer
     * @return void
     */
    public function offerAnswer(Offer $offer, $answer)
    {
        // TODO: Implement offerAnswer() method.
    }

    /**
     * Retrieve an RTC offer with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Offer|null 
     */
    public function getOffer($name)
    {
        $fh = fopen($this->getStorePath(), 'r');
        $offer = null;
        while (FALSE !== ($row = fgetcsv($fh))){
            if ((count($row) == 5) && $row[0] == $name){
                $offer = new Offer();
                $offer->setName($row[0]);
                $offer->setCandidate($row[1]);
                $offer->setContent($row[2]);
                $offer->setAnswer($row[3]);
                $offer->setUsed(!!$row[4]);
                break;
            }
        }
        return $offer;
    }
    
    private function save(Offer $offer)
    {
        $fh = fopen($this->getStorePath(), 'a');
        $row = [
            $offer->getName(),
            $offer->getCandidate(),
            $offer->getContent(),
            $offer->getAnswer(),
            $offer->getUsed() ? '1' : '0'
        ];
        fputcsv($fh, $row);
        fclose($fh);
    }
}