<?php

namespace Universal\IDTBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testBillinghistory()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/billing-history');
    }

    public function testOrderdetails()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/order');
    }

    public function testMypins()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/my-pins');
    }

    public function testPindetails()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/details');
    }

}
