<?php

namespace classes;

use Silex\Application;
use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use bandwidthThrottle\tokenBucket\storage\PHPRedisStorage;
use provider\PHPRedisServiceProvider;

class RateLimit
{
    private $redisObj = null;
    private $globalBucket = null;
    private $userBucket = null;
    private $globalFillRate;
    private $userFillRate;
    private $user = null;

    const MAXBUCKETSIZE = 10;
    const CONSUMEPERREQUEST = 1;

    public function __construct($app)
    {
        $this->redisObj = $app['redis'];
        $this->globalFillRate    = new Rate(100, Rate::SECOND);
        $this->userFillRate    = new Rate(1, Rate::SECOND);
        $this->createBucket('global');
    }

    public function connect()
    {
        //$this->redisObj = new \Redis;
        //$this->redisObj->connect('localhost', 6379);

    }

    private function createBucket($type)
    {
        switch ($type) {
            case 'global':
                    $this->globalBucket = $this->initBucket($type, $this->globalFillRate);
                break;
            default:
                if (isset($type)) {
                    $this->userBucket = $this->initBucket($type, $this->userFillRate);
                }
                break;
        }
    }

    private function initBucket($type, $fillRate)
    {
        $storage = new PHPRedisStorage($type, $this->redisObj);
        $bucket  = new TokenBucket(self::MAXBUCKETSIZE, $fillRate, $storage);
        $bucket->bootstrap(self::MAXBUCKETSIZE);
        return $bucket;
    }

    public function setUser($user)
    {
        $this->user = $user;
        $this->createBucket($this->user);
    }

    /**
     * Consumes tokens from the bucket.
     *
     * This method consumes only tokens if there are sufficient tokens available.
     * If there aren't sufficient tokens, no tokens will be removed and the
     * remaining seconds to wait are written to $seconds.
     *
     * This method is threadsafe.
     *
     * @param object $bucket   The bucket to use.
     * @param double &$seconds The seconds to wait.
     *
     * @return bool If tokens were consumed.
     *
     * @throws \LengthException The token amount is larger than the capacity.
     * @throws StorageException The stored microtime could not be accessed.
     */
    private function consumeOne($bucket, &$seconds = 0)
    {
        return $bucket->consume(self::CONSUMEPERREQUEST, $seconds);
    }

    /**
     * Attempts to consume tokens from both the global and user bucket.
     *
     * @param double &$seconds The seconds to wait.
     *
     * @return bool If tokens were consumed.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @throws \LengthException The token amount is larger than the capacity.
     * @throws StorageException The stored microtime could not be accessed.
     */
    public function consumeAll(&$seconds = 0)
    {
        $global = true;
        $user = true;
        $allowed = false;
        $global = $this->consumeOne($this->globalBucket, $seconds);
        $retry = $seconds;
        if (isset($this->user)) {
            $user = $this->consumeOne($this->userBucket, $seconds);
            if ($retry > $seconds) {
                $seconds = $retry;
            }
        }
        if ($global and $user) {
            $allowed = true;
        }
        return $allowed;
    }
}
