<?php
use DataFestivus\Controller;

/**
 * Created by PhpStorm.
 * User: seth
 * Date: 4/25/16
 * Time: 7:35 PM
 */

class ControllerTest extends PHPUnit_Framework_TestCase {
    
    private $controller;
    private $storeMockBuilder;
    
    public function setUp()
    {
        $this->storeMockBuilder = $this
            ->getMockBuilder('\DataFestivus\RTCStore\RTCStore')
            ->disableOriginalConstructor();
        
        $rtcStore = $this->storeMockBuilder->getMock();
        
        $this->controller = new Controller($rtcStore);
    }
    
    public function testBasic()
    {
        $this->assertInstanceOf('\DataFestivus\Controller', $this->controller);
        
    }
    
    public function testSignalOffer()
    {
        $offerRTCConn = $this->getMock('\DataFestivus\RTCSTore\Connection');
        $offer = ['foo' => 'bar', 'baz' => 42];
        $offerStore = $this->storeMockBuilder->getMock();
        $offerStore
            ->expects($this->once())
            ->method('getOffer')
            ->with('test')
            ->willReturn(null);
        $offerStore
            ->expects($this->once())
            ->method('offerCreate')
            ->with('test', json_encode($offer))
            ->willReturn($offerRTCConn);

        list($code, $message, $connection, $errors) = Controller::signal([
                'call' => Controller::CALL_OFFER,
                'name' => 'test', 
                'connection' => json_encode(['offer' => $offer])
            ], $offerStore);
        
        $this->assertEquals(200, $code);
        $this->assertEquals('success', $message);
        $this->assertSame($offerRTCConn, $connection);
        $this->assertCount(0, $errors);
        
    }

    public function testSignalAnswer()
    {
        $answerRTCConn = $this->getMock('\DataFestivus\RTCSTore\Connection');
        $answer = ['foo' => 'bar', 'baz' => 42];
        $answerStore = $this->storeMockBuilder->getMock();
        $answerStore
            ->expects($this->once())
            ->method('getOffer')
            ->with('test')
            ->willReturn($answerRTCConn);
        $answerStore
            ->expects($this->once())
            ->method('offerAnswer')
            ->with($answerRTCConn, json_encode($answer))
            ->willReturn($answerRTCConn);

        list($code, $message, $connection, $errors) = Controller::signal([
            'call' => Controller::CALL_ANSWER,
            'name' => 'test',
            'connection' => json_encode(['answer' => $answer])
        ], $answerStore);

        $this->assertEquals(200, $code);
        $this->assertEquals('success', $message);
        $this->assertSame($answerRTCConn, $connection);
        $this->assertCount(0, $errors);

    }

    public function testSignalFetch()
    {
        $fetchRTCConn = $this->getMock('\DataFestivus\RTCSTore\Connection');
        $fetchStore = $this->storeMockBuilder->getMock();
        $fetchStore
            ->expects($this->once())
            ->method('getOffer')
            ->with('test')
            ->willReturn($fetchRTCConn);

        list($code, $message, $connection, $errors) = Controller::signal([
            'call' => Controller::CALL_FETCH,
            'name' => 'test'
        ], $fetchStore);

        $this->assertEquals(200, $code);
        $this->assertEquals('success', $message);
        $this->assertSame($fetchRTCConn, $connection);
        $this->assertCount(0, $errors);

    }
    public function testSignalInvalid()
    {
        $fetchStore = $this->storeMockBuilder->getMock();

        list($code, $message, $connection, $errors) = Controller::signal([
            'call' => 'blahblahblah',
            'name' => 'test'
        ], $fetchStore);

        $this->assertEquals(400, $code);
        $this->assertEquals('invalid call', $message);
        $this->assertNull($connection);
        $this->assertArrayHasKey('call', $errors);

    }
    
    public function testOffer()
    {
        
    }
    
    public function signalProvider()
    {
        $data = []; 
        
        return $data;
    }
}
