<?php

namespace Tests\provider;

use Silex\Application;
use provider\PHPRedisServiceProvider;
use provider\RateLimitServiceProvider;

/**
 * RateLimitServiceProvider test cases.
 *
 * @author Thomas Cooper <>
 */
class RateLimitServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterAndCreateUserBucket()
    {
        $app = new Application();

        $redisMock = $this->getMockBuilder('\Redis')
            ->disableOriginalConstructor()
            ->setMethods(array('exists'))
            ->getMock();

        $redisMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue('testguy'));

        $app->register(new PHPRedisServiceProvider(), array(
            'redis.engine' => $redisMock
        ));
        $app->register(new RateLimitServiceProvider());
        $bucket = $app['ratelimit']->createUserBucket('testguy');

        $this->assertInstanceOf('bandwidthThrottle\tokenBucket\TokenBucket', $bucket);

    }
}
