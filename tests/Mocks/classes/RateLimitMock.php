<?php

namespace Mocks\classes;

use classes\RateLimit;

class RateLimitMock extends \PHPUnit_Framework_TestCase
{
    private $mockedClass;

    public function __construct()
    {
        $this->mockedClass = $this->getMockBuilder('classes\RateLimit')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'createUserBucket',
                'consumeAll'
            ))
            ->getMock();
    }
    public function getBase()
    {
        $this->mockedClass->expects($this->once())
            ->method('consumeAll')
            ->will($this->returnValue(true));
        return $this->mockedClass;
    }
}
