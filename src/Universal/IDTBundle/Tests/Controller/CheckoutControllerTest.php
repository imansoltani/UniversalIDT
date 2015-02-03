<?php

namespace Universal\IDTBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CheckoutControllerTest extends WebTestCase
{
    public function testMycart()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/MyCart');
    }

}
