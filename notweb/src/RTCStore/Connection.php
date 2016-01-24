<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 1/2/16
 * Time: 7:27 PM
 */

namespace DataFestivus\RTCStore;

/**
 * Class Connection - Models the fields required for an RTC offer
 * @package RTCStore
 */
class Connection implements \JsonSerializable
{
    private $name = '';
    private $offer = null;
    private $answer = null;
    private $candidates = [];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param string $offer
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;
    }

    /**
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    /**
     * @return string[] RTCIceCandidate json strings
     */
    public function getCandidates()
    {
        return $this->candidates;
    }

    /**
     * @param string[] $answerCandidate json strings
     */
    public function setCandidates(array $answerCandidates)
    {
        $this->candidates = $answerCandidates;
    }
    
    public function addCandidate($candidate)
    {
        foreach ($this->candidates as $c){
            if ($candidate == $c) {
                return;
            }
        }
        $this->candidates[] = $candidate;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        $offer = $this->getOffer();
        $answer = $this->getAnswer();
        $candidates = [];
        foreach ($this->getCandidates() as $candidate){
            $candidates[] = json_decode($candidate, true);
        }
        $result = [
            'name' => $this->getName(),
            'offer' => ($offer ? json_decode($offer, true) : ''),
            'answer' => ($answer ? json_decode($answer, true) : ''),
            'candidates' => $candidates
        ];
        return $result;
    }
}