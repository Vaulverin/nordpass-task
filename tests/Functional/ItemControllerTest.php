<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\UserFixture;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class ItemControllerTest extends WebTestCase
{
    /**
     * @var UserRepository
     */
    protected $userRepository;
    /**
     * @var KernelBrowser
     */
    protected $client;
    /**
     * @var User
     */
    protected $user;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->userRepository = static::$container->get(UserRepository::class);
        $this->user = $this->userRepository->findOneByUsername(UserFixture::TEST_USERNAME);
        $this->client->loginUser($this->user);
    }

    protected function getItems()
    {
        $this->client->request('GET', '/item');
        $this->assertResponseIsSuccessful('Get items request has failed');
        $items = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($items, 'Response does not contain JSON array');
        return $items;
    }

    public function testCreate()
    {
        $currentState = $this->getItems();
        $data = 'very secure new item data';
        $newItemData = ['data' => $data];

        $this->client->request('POST', '/item', $newItemData);
        $this->assertResponseIsSuccessful('Create item request failed');

        $newState = $this->getItems();
        $this->assertCount(
            count($currentState) + 1,
            $newState,
            'The amount of items has not changed after the update'
        );
        $itemsDiffCallback = function ($itemA, $itemB) {
            if ($itemA['id'] < $itemB['id']) return -1;
            if ($itemA['id'] > $itemB['id']) return 1;
            return 0;
        };
        $itemsDiff = array_udiff($newState, $currentState, $itemsDiffCallback);
        $this->assertCount(1, $itemsDiff, 'Exactly one item has to be created');
        $this->assertEquals($data, array_pop($itemsDiff)['data']);
    }

    public function testUpdate()
    {
        $items = $this->getItems();
        $this->assertNotCount(0, $items, 'No items to update');
        $updatedItemId = $items[0]['id'];
        $data = 'new item data';
        $itemData = [
            'id' => $updatedItemId,
            'data' => $data
        ];

        $this->client->request('PUT', '/item', $itemData);
        $this->assertResponseIsSuccessful('Update item request has failed');

        $updatedItems = $this->getItems();
        $this->assertCount(count($items), $updatedItems, 'The amount of items has changed after the update');
        foreach ($updatedItems as $item) {
            if ($item['id'] === $updatedItemId) {
                $this->assertEquals($data, $item['data'], 'Updated item data has unexpected value');
                return;
            }
        }
        $this->fail('There is no Item with id = ' . $updatedItemId);
    }

    public function testDelete()
    {
        $items = $this->getItems();
        $this->assertNotCount(0, $items, 'No items to delete');
        $deletedItemId = $items[0]['id'];

        $this->client->request('DELETE', '/item/' . $deletedItemId);
        $this->assertResponseIsSuccessful('Delete item request has failed');

        $newState = $this->getItems();
        $this->assertCount(
            count($items) - 1,
            $newState,
            'The amount of items have to be less by one item then before DELETE request'
        );
        foreach ($newState as $item) {
            if ($item['id'] === $deletedItemId) {
                $this->fail('Item has not been deleted');
            }
        }
    }
}
