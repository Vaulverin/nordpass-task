<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ItemControllerNoUserTest extends WebTestCase
{
    public function testRequestsWithNoUser()
    {
        $client = static::createClient();
        $item = ['id' => 1, 'data' => 'secret data'];
        $requests = [
            'Get item' => ['GET', '/item', []],
            'Create item' => ['POST', '/item', $item],
            'Update item' => ['PUT', '/item', $item],
            'Delete item' => ['DELETE', '/item/1', []]
        ];
        foreach ($requests as $name => $info) {
            $client->request($info[0], $info[1], $info[2]);
            $this->assertEquals(
                401,
                $client->getResponse()->getStatusCode(),
                $name . ' request have to return Access Denied error'
            );
        }
    }
}
