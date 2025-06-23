<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Xutim\CoreBundle\Domain\Model\UserInterface;
use Xutim\CoreBundle\Service\CsrfTokenChecker;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

class DeleteRedirectAction
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly RedirectRepositoryInterface $repo,
        private readonly UrlGeneratorInterface $router,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly FlashNotifier $flashNotifier
    ) {
    }

    public function __invoke(string $id, Request $request): Response
    {
        if ($this->authChecker->isGranted(UserInterface::ROLE_EDITOR) === false) {
            throw new AccessDeniedException('Access denied.');
        }

        $redirect = $this->repo->findById($id);
        if ($redirect === null) {
            throw new NotFoundHttpException('The redirect does not exist');
        }
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);

        $this->repo->remove($redirect, true);
        $this->flashNotifier->changesSaved();

        return new RedirectResponse(
            $this->router->generate('admin_redirect_list', ['searchTerm' => ''])
        );
    }
}
