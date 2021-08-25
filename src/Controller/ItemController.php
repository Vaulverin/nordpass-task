<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Item;
use App\Service\ItemService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ItemController extends AbstractController
{
    /**
     * @param Request $request
     * @return string|JsonResponse
     */
    protected function validateDataParameter(Request $request)
    {
        $data = $request->get('data');

        if (empty($data)) {
            throw new BadRequestHttpException('No data parameter');
        }
        return $data;
    }
    /**
     * @Route("/item", name="items_list", methods={"GET"}, format="json")
     * @IsGranted("ROLE_USER")
     */
    public function list(): JsonResponse
    {
        $items = $this->getDoctrine()->getRepository(Item::class)->findBy(['user' => $this->getUser()]);

        $allItems = [];
        foreach ($items as $item) {
            $oneItem['id'] = $item->getId();
            $oneItem['data'] = $item->getData();
            $oneItem['created_at'] = $item->getCreatedAt();
            $oneItem['updated_at'] = $item->getUpdatedAt();
            $allItems[] = $oneItem;
        }

        return $this->json($allItems);
    }

    /**
     * @Route("/item", name="item_create", methods={"POST"}, format="json")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, ItemService $itemService): JsonResponse
    {
        $data = $this->validateDataParameter($request);

        $itemService->create($this->getUser(), $data);

        return $this->json([]);
    }

    /**
     * @Route("/item", name="item_update", methods={"PUT"}, format="json")
     * @IsGranted("ROLE_USER")
     */
    public function update(Request $request, ItemService $itemService): JsonResponse
    {
        $id = $request->get('id');
        if (empty($id)) {
            throw new BadRequestException('No id parameter');
        }
        $data = $this->validateDataParameter($request);

        $itemService->update($this->getUser(), (int)$id, $data);

        return $this->json([]);
    }
    
    /**
     * @Route("/item/{id}", name="item_delete", methods={"DELETE"}, format="json")
     * @IsGranted("ROLE_USER")
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        if (empty($id)) {
            throw new BadRequestException('No id parameter');
        }

        $item = $this->getDoctrine()->getRepository(Item::class)->findOneBy([
            'user' => $this->getUser(),
            'id' => $id
        ]);

        if ($item === null) {
            throw new BadRequestException('No item');
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($item);
        $manager->flush();

        return $this->json([]);
    }
}
