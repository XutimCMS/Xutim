<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\BlockItem;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/block/remove-item/{id}', name: 'admin_block_remove_item')]
class RemoveBlockItemAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly BlockItemRepository $blockItemRepository,
        private readonly BlockContext $blockContext
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $blockItem = $this->blockItemRepository->find($id);
        if ($blockItem === null) {
            throw $this->createNotFoundException('The item does not exist');
        }
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $blockCode = $blockItem->getBlock()->getCode();
        $this->blockItemRepository->remove($blockItem, true);
        $this->blockContext->resetAllLocalesBlockTemplate($blockCode);
        $this->addFlash('success', 'flash.changes_made_successfully');

        return $this->redirect($request->headers->get('referer', ''));
    }
}
