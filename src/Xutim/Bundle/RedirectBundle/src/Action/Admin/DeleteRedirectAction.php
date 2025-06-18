<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Model\UserInterface;
use Xutim\CoreBundle\Service\CsrfTokenChecker;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

#[Route('/admin/redirect/delete/{id}', name: 'admin_redirect_delete')]
class DeleteRedirectAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly RedirectRepositoryInterface $repo,
    ) {
    }

    public function __invoke(string $id, Request $request): Response
    {
        $redirect = $this->repo->find($id);
        if ($redirect === null) {
            throw $this->createNotFoundException('The redirect does not exist');
        }
        $this->denyAccessUnlessGranted(UserInterface::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        $this->repo->remove($redirect);

        $this->addFlash('success', 'flash.changes_made_successfully');

        return $this->redirectToRoute('admin_redirect_list', ['searchTerm' => '']);
    }
}
