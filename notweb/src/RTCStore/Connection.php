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
class Connection
{
    private $name = '';
    private $offer = '';
    private $answer = '';
    private $candidate = '';

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
     * @return string
     */
    public function getCandidate()
    {
        return $this->candidate;
    }

    /**
     * @param string $answerCandidate
     */
    public function setCandidate($answerCandidate)
    {
        $this->candidate = $answerCandidate;
    }
    
}