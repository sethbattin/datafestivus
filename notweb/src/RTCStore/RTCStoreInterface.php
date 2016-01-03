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
     * @param $name
     * @param $candidate
     * @param $content
     * @return \DataFestivus\RTCStore\Connection
     */
    public function offerCreate($name, $candidate, $content);

    /**
     * Answer an existing offer and save its connection.
     * @param Connection $connection
     * @param string $answer - answer from RTCConnection
     * @param string $candidate - ice candidate from answerer
     * @return void
     */
    public function offerAnswer(Connection $connection, $answer, $candidate);

    /**
     * Retrieve an RTC connection with the specified name
     * @param $name
     * @return \DataFestivus\RTCStore\Connection
     */
    public function getOffer($name);
}