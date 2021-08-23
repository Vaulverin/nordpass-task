<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(User $user, string $data): ItemService
    {
        $item = new Item();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
        
        return $this;
    }
    
    public function update(User $user, int $id, string $data): ItemService
    {
        /** @var Item $item */
        $item = $this->entityManager->getRepository(Item::class)->findOneBy([
            'user' => $user,
            'id' => $id
        ]);
        if($item === null) {
            throw new NotFoundHttpException('Item not found');
        }
        $item->setData($data);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this;
    }
} 