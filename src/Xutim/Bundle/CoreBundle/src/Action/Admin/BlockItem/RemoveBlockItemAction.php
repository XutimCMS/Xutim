<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\BlockItem;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;

class RemoveBlockItemAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly BlockItemRepository $blockItemRepository,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $blockItem = $this->blockItemRepository->find($id);
        if ($blockItem === null) {
            throw $this->createNotFoundException('The item does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('xutim-dialog', $request);
        $this->blockItemRepository->remove($blockItem, true);
        $this->addFlash('success', 'flash.changes_made_successfully');

        return $this->redirect($request->headers->get('referer', ''));
    }
}
