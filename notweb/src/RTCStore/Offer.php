<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 1/2/16
 * Time: 7:27 PM
 */

namespace DataFestivus\RTCStore;

/**
 * Class Offer - Models the fields required for an RTC offer
 * @package RTCStore
 */
class Offer
{
    private $name = '';
    private $candidate = '';
    private $content = '';
    private $answer = '';
    private $used = '';

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
    public function getCandidate()
    {
        return $this->candidate;
    }

    /**
     * @param string $candidate
     */
    public function setCandidate($candidate)
    {
        $this->candidate = $candidate;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
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
     * @return boolean
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * @param boolean $used
     */
    public function setUsed($used)
    {
        $this->used = $used;
    }
}