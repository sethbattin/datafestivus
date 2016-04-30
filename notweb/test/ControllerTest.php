<?php
use DataFestivus\Controller;
use DataFestivus\RTCStore\RTCStore;
use DataFestivus\RTCStore\Connection;

/**
 * Created by PhpStorm.
 * User: seth
 * Date: 4/25/16
 * Time: 7:35 PM
 */

class ControllerTest extends PHPUnit_Framework_TestCase {

    /** @var Controller */
    private $controller;
    private $storeMockBuilder;
    
    /** @var  RTCStore */
    private $rtcStore;
    
    public function setUp()
    {
        $this->storeMockBuilder = $this
            ->getMockBuilder('\DataFestivus\RTCStore\RTCStore')
            ->disableOriginalConstructor();
        
        $this->rtcStore = $this->storeMockBuilder->getMock();
        $this->controller = new Controller($this->rtcStore);
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

    /**
     * @dataProvider offerProvider
     * @param string $name
     * @param string $connJson
     * @param integer $code
     * @param Connection|null $initRTC
     * @param Connection|null $RTCconn
     */
    public function testOffer($name, $connJson, $code, Connection $initRTC = null, Connection $RTCconn = null)
    {
        $this->rtcStore->method('getOffer')->willReturn($initRTC);
        $this->rtcStore->method('offerCreate')->willReturn($RTCconn);
        $this->controller->offer($name, $connJson);
        list($_code, $message, $connection, $errors) = 
            $this->controller->getCallResult();
        
        $this->assertEquals($code, $_code);
        $this->assertSame($RTCconn, $connection);
    }

    /**
     * @dataProvider answerProvider
     * @param string $name
     * @param string $connJson
     * @param integer $code
     * @param Connection|null $RTCconn
     */
    public function testAnswer($name, $connJson, $code, Connection $RTCconn = null)
    {
        $this->rtcStore->method('getOffer')->willReturn($RTCconn);
        $this->controller->answer($name, $connJson);
        list($_code, $message, $connection, $errors) =
            $this->controller->getCallResult();
        
        $this->assertEquals($code, $_code, sprintf("Expected result code %d, received %d (%d).", $code, $_code, $message));
        $this->assertSame($connection, $RTCconn);
        
    }

    /**
     * @dataProvider fetchProvider
     * @param string $name
     * @param Connection $connection
     * @param integer $code
     */
    public function testFetch($name, Connection $connection = null, $code)
    {
        $this->rtcStore->method('getOffer')->willReturn($connection);
        $this->controller->fetch($name);
        list($_code, $message, $_connection, $errors) =
            $this->controller->getCallResult();
        
        $this->assertEquals($code, $_code);
        $this->assertSame($connection, $_connection);
    }
    
    public function offerProvider()
    {
        $data = []; 
        
        $data['plain'] = [
            'test',
            json_encode(['offer' => ['foo' => 'bar', 'baz' => 42]]),
            200,
            null,
            new Connection()
        ];
        $data['exists'] = [
            'test',
            json_encode(['offer' => ['foo' => 'bar', 'baz' => 42]]),
            400,
            new Connection(),
            null
        ];
        $data['malform'] = [
            'test',
            "{sjlkaskjlafsd I AM JSONLOL",
            400,
            new Connection(),
            null
        ];
        $data['no offer'] = [
            'test',
            "{sjlkaskjlafsd: \"I AM JSONLOL\"}",
            400,
            new Connection(),
            null
        ];
        
        return $data;
    }

    public function answerProvider()
    {
        $data = [];

        $data['plain'] = [
            'test',
            json_encode(['answer' => ['foo' => 'bar', 'baz' => 42]]),
            200,
            new Connection()
        ];
        
        $data['not found'] = [
            'test',
            json_encode(['answer' => ['foo' => 'bar', 'baz' => 42]]),
            404,
            null
        ];
        $data['malform'] = [
            'test',
            "{sjlkaskjlafsd I AM JSONLOL",
            400,
            null
        ];
        $data['nothing'] = [
            'test',
            false,
            400,
            null
        ];
        $data['no answer'] = [
            'test',
            "{sjlkaskjlafsd: \"I AM JSONLOL\"}",
            400,
            null
        ];

        return $data;
    }
    
    public function fetchProvider()
    {
        $data = [];
        
        $data['found'] = ['found', new Connection(), 200];
        
        $data['not found'] = ['found', null, 404];
        
        return $data;
    }
}
