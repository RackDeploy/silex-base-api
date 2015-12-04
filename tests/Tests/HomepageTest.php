<?php

namespace Tests;

use App\SetupApp;
use Mocks\classes\RateLimitMock;

class HomepageTest extends SetupApp
{
    public function testHomepage()
    {
        $client = $this->createClient(array('HTTP_HOST' => '192.168.169.13'));
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Welcome to your")'));
    }
    public function testRateLimited()
    {
        $rateMock = new RateLimitMock();
        $this->mockProvider('ratelimit', $rateMock->setRateLimited(true));

        $response = '{"message":"Too many requests, try again in 0 seconds.","retry-after":0}';
        $fullResponse = $this->failedResponse($response, 429);

        $client = $this->createClient(array('HTTP_HOST' => '192.168.169.13'));
        $client->request('GET', '/status');

        $this->assertEquals($client->getResponse()->getStatusCode(), 429);
        $this->assertEquals($client->getResponse()->getContent(), $fullResponse);
    }
    public function testNotFound()
    {
        // turn off debug mode to test actual production 404
        $this->mockProvider('debug', false);

        $client = $this->createClient(array('HTTP_HOST' => '192.168.169.13'));
        $crawler = $client->request('GET', '/not_found');

        $this->assertTrue($client->getResponse()->isNotFound());
        $this->assertCount(1, $crawler->filter('h1:contains("Page not found.")'));
    }
    /**
    * @expectedException        Symfony\Component\HttpKernel\Exception\NotFoundHttpException
    * @expectedExceptionMessage No route found for "GET /not_found"
    */
    public function testRouteNotFoundException()
    {
        $client = $this->createClient(array('HTTP_HOST' => '192.168.169.13'));
        $client->request('GET', '/not_found');

    }
}
