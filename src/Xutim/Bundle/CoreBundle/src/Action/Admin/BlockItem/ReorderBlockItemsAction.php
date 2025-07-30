<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\BlockItem;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Entity\BlockItem;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Repository\BlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;

class ReorderBlockItemsAction extends AbstractController
{
    public function __construct(
        private readonly BlockItemRepository $blockItemRepo,
        private readonly BlockRepository $blockRepo,
        private readonly BlockContext $blockContext
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $block = $this->blockRepo->find($id);
        if ($block === null) {
            throw $this->createNotFoundException('The block does not exist');
        }

        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $startPos = $request->query->getInt('startPos');
        $endPos = $request->query->getInt('endPos');

        try {
            /** @var BlockItem $item */
            $item = $this->blockItemRepo->findOneBy(['position' => $startPos, 'block' => $block]);
            $item->changePosition($endPos);
            $this->blockItemRepo->save($item, true);
            $this->blockContext->resetAllLocalesBlockTemplate($block->getCode());
        } catch (Exception) {
            return new JsonResponse(false);
        }
        return new JsonResponse(true);
    }
}
