<?php

namespace Tests\API;

use App\SetupApp;

class StatusTest extends SetupApp
{
    public function testInitialPage()
    {
        $response = '{"success":true}';
        $fullResponse = $this->successfulResponse($response, 200);

        $client = $this->createClient(array('HTTP_HOST' => '192.168.169.13'));
        $client->request('GET', '/status');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals($client->getResponse()->getContent(), $fullResponse);
    }
}
