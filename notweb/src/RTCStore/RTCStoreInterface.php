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
     * Create an RTC connection instance and save it.
     * @param string $name
     * @param string $offer - RTCOffer json
     * @return \DataFestivus\RTCStore\Connection
     */
    public function offerCreate($name, $offer);

    /**
     * Answer an existing offer and save its connection.
     * @param Connection $connection
     * @param string $answer - RTCOfferAnswer json
     * @return void
     */
    public function offerAnswer(Connection $connection, $answer);

    /**
     * @param Connection $connection
     * @param string $candidate - RTCIceCandidate json
     * @return void
     */
    public function addIceCandidate(Connection $connection, $candidate);

    /**
     * Retrieve an RTC connection with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Connection
     */
    public function getOffer($name);
}