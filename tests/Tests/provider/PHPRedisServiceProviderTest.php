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
class PHPRedisServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterAndOptions()
    {
        $app = new Application();

        $app->register(new PHPRedisServiceProvider(), array(
            'redis.engine' => $this->buildMock(),
            // 'redis.host' => '127.0.0.1',
            // 'redis.port' => 6379,
            // 'redis.timeout' => 30,
            // 'redis.persistent' => true,
            // 'redis.serializer.igbinary' => false, // use igBinary serialize/unserialize
            // 'redis.serializer.php' => false, // use built-in serialize/unserialize
            // 'redis.prefix' => 'myprefix',
            // 'redis.database' => '0'
        ));
        $app->register(new RateLimitServiceProvider());
        $bucket = $app['ratelimit']->createUserBucket('testguy');

        $this->assertInstanceOf('bandwidthThrottle\tokenBucket\TokenBucket', $bucket);

        unset($app['redis']);
        $app->register(new PHPRedisServiceProvider(), array(
            'redis.engine' => $this->buildMock(),
            'redis.host' => '127.0.0.1'
        ));
        $bucket = $app['ratelimit']->createUserBucket('testguy');
        $this->assertInstanceOf('bandwidthThrottle\tokenBucket\TokenBucket', $bucket);

        $app->register(new PHPRedisServiceProvider(), array(
            'redis.engine' => $this->buildMock(),
            'redis.persistent' => true
        ));
        $bucket = $app['ratelimit']->createUserBucket('testguy');
        $this->assertInstanceOf('bandwidthThrottle\tokenBucket\TokenBucket', $bucket);
    }
    public function testRegisterAndPersistent()
    {
        $app = new Application();



        $app->register(new PHPRedisServiceProvider(), array(
            'redis.engine' => $this->buildMock(),
            'redis.persistent' => true,
        ));
        $app->register(new RateLimitServiceProvider());
        $bucket = $app['ratelimit']->createUserBucket('testguy');

        $this->assertInstanceOf('bandwidthThrottle\tokenBucket\TokenBucket', $bucket);
    }

    private function buildMock()
    {
        $redisMock = $this->getMockBuilder('\Redis')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'exists',
                'setOption'
            ))
            ->getMock();

        $redisMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue('testguy'));
        $redisMock->expects($this->any())
            ->method('setOption')
            ->will($this->returnValue(true));
        return $redisMock;
    }
}
