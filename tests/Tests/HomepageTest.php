<?php

namespace Tests;

use App\SetupApp;

class HomepageTest extends SetupApp
{
    public function testInitialPage()
    {
        $client = $this->createClient(array('HTTP_HOST' => '192.168.169.13'));
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Welcome to your")'));
    }
}
