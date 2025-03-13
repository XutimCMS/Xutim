<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\BlockItem;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Entity\Block;
use Xutim\CoreBundle\Entity\BlockItem;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\BlockItemRepository;

#[Route('/block/reorder/{id}', name: 'admin_block_reorder_item')]
class ReorderBlockItemsAction extends AbstractController
{
    public function __construct(
        private readonly BlockItemRepository $repo,
        private readonly BlockContext $blockContext
    ) {
    }

    public function __invoke(Request $request, Block $block): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $startPos = $request->query->getInt('startPos');
        $endPos = $request->query->getInt('endPos');

        try {
            /** @var BlockItem $item */
            $item = $this->repo->findOneBy(['position' => $startPos, 'block' => $block]);
            $item->changePosition($endPos);
            $this->repo->save($item, true);
            $this->blockContext->resetAllLocalesBlockTemplate($block->getCode());
        } catch (Exception) {
            return new JsonResponse(false);
        }
        return new JsonResponse(true);
    }
}
