<?php
/**
 * RateLimiting based on the Token Algorithm (or LeakyBucket)
 *
 * Automatically configures a global bucket for rate limiting, then allows you to define additional buckets with
 * setUser method.
 *
 * @package omni-api
 * @author Thomas Cooper
 * @version 1.0
 * @copyright closed-source
 */

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

    /**
     * Pull in Redis provider, set global and user fillrate, then create the global token bucket.
     * @param Application $app Silex Application
     */
    public function __construct(Application $app)
    {
        $this->redisObj = $app['redis'];
        $this->globalFillRate    = new Rate(100, Rate::SECOND);
        $this->userFillRate    = new Rate(1, Rate::SECOND);
        $this->createBucket('global');
    }

    /**
     * This method creates a token bucket based on the provided Requires the global and user fillrate to be preset
     * @param  string       $bucketName Either 'global' or the username used to create a token bucket
     * @return TokenBucket              The initialized bucket
     * @access private
     */
    private function createBucket($bucketName)
    {
        $bucket = null;
        switch ($bucketName) {
            case 'global':
                    $bucket = $this->initializeBucket($bucketName, $this->globalFillRate);
                    $this->globalBucket = $bucket;
                break;
            default:
                if (isset($bucketName)) {
                    $bucket = $this->initializeBucket($bucketName, $this->userFillRate);
                    $this->userBucket = $bucket;
                }
                break;
        }
        return $bucket;
    }

    /**
     * Initialize the a new token bucket, then proload that bucket with tokens
     * @param  string       $bucketName The name of the bucket to create
     * @param  Rate         $fillRate   Rate Object used to define fill rate
     * @return TokenBucket              The initialized bucket
     * @access private
     */
    private function initializeBucket($bucketName, Rate $fillRate)
    {
        $storage = new PHPRedisStorage($bucketName, $this->redisObj);
        $bucket  = new TokenBucket(self::MAXBUCKETSIZE, $fillRate, $storage);
        $bucket->bootstrap(self::MAXBUCKETSIZE);
        return $bucket;
    }


    /**
     * Create a new token bucket for the provided username, uses default fill rate
     * @param  string       $username   Username to create a bucket for
     * @return TokenBucket              The initialized bucket
     * @access public
     */
    public function createUserBucket($username)
    {
        $this->user = $username;
        return $this->createBucket($this->user);
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
