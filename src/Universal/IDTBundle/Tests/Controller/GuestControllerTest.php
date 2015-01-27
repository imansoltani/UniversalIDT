<?php

namespace Universal\IDTBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GuestControllerTest extends WebTestCase
{
    public function testMain()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
    }

    public function testCallingcards()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/calling_cards');
    }

    public function testDetails()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/country');
    }

    public function testRates()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/rates');
    }

    public function testShopping()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/shopping');
    }

}
