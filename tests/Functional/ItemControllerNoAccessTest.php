<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\UserFixture;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Service\ItemService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ItemControllerNoAccessTest extends WebTestCase
{
    /**
     * @var User|null
     */
    protected $user;
    /**
     * @var UserRepository|null
     */
    protected $userRepository;
    /**
     * @var KernelBrowser
     */
    protected $client;
    /**
     * @var int
     */
    protected $otherUserItemId;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->userRepository = static::$container->get(UserRepository::class);
        $this->user = $this->userRepository->findOneByUsername(UserFixture::TEST_USERNAME);
        $this->client->loginUser($this->user);

        $this->userRepository = static::$container->get(UserRepository::class);
        $otherUser = $this->userRepository->findOneByUsername(UserFixture::TEST_USERNAME_OTHER);
        /** @var ItemRepository $itemRepository */
        $itemRepository = static::$container->get(ItemRepository::class);
        $item = $itemRepository->findOneBy([
            'user' => $otherUser,
        ]);
        if ($item === null) {
            /** @var ItemService $itemService */
            $itemService = static::$container->get(ItemService::class);
            $item = $itemService->create($otherUser, 'some secret data');
        }
        $this->otherUserItemId = $item->getId();
    }

    public function testRequestsWithNoAccess()
    {
        $requests = [
            'Update item' => ['PUT', '/item', [
                'id' => $this->otherUserItemId,
                'data' => 'some new data'
            ]],
            'Delete item' => ['DELETE', '/item/'.$this->otherUserItemId, []]
        ];
        foreach ($requests as $name => $info) {
            $this->client->request($info[0], $info[1], $info[2]);
            $this->assertNotEquals(
                200,
                $this->client->getResponse()->getStatusCode(),
                $name. ' request have to return error for user without access to this item'
            );
        }
    }
}
