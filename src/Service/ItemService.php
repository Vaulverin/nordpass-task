<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
        $item = $this->entityManager->find(Item::class, $id);
        if($item === null) {
            throw new NotFoundHttpException('Item not found');
        }
        if($item->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('Sorry, you don\'t have access to this item');
        }
        $item->setData($data);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this;
    }
} 