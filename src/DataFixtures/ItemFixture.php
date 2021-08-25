<?php

namespace App\DataFixtures;

use App\Entity\Item;
use App\Repository\UserRepository;
use App\Service\ItemService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ItemFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * @var ItemService
     */
    protected $itemService;
    /**
     * @var UserRepository
     */
    protected $userRepository;

    public function __construct(ItemService $itemService, UserRepository $userRepository) {
        $this->itemService = $itemService;
        $this->userRepository = $userRepository;
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class
        ];
    }

    public function load(ObjectManager $manager)
    {
        $user = $this->userRepository->findOneByUsername(UserFixture::TEST_USERNAME_OTHER);
        $this->itemService->create($user, 'other John\'s data');
    }
}