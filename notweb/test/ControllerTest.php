<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 4/25/16
 * Time: 7:35 PM
 */

class ControllerTest extends PHPUnit_Framework_TestCase {
    
    private $controller;
    
    public function setUp()
    {
        $rtcStore = $this->getMockBuilder('\DataFestivus\RTCStore\RTCStore')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new \DataFestivus\Controller($rtcStore);
    }
    
    public function testBasic()
    {
        $this->assertInstanceOf('\DataFestivus\Controller', $this->controller);
    }
}
