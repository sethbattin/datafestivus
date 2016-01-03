<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 1/2/16
 * Time: 6:58 PM
 */
namespace DataFestivus\RTCStore;

interface RTCStoreInterface {
    /**
     * Create an RTC offer instance and save it.
     * @param $name
     * @param $candidate
     * @param $content
     * @return \DataFestivus\RTCStore\Offer
     */
    public function offerCreate($name, $candidate, $content);

    /**
     * Answer an existing offer and save it.
     * @param Offer $offer
     * @param $answer
     * @return void
     */
    public function offerAnswer(Offer $offer, $answer);

    /**
     * Retrieve an RTC offer with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Offer
     */
    public function getOffer($name);
}