<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetTranslationRepositoryInterface;

class DeleteSnippetAction
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly SnippetRepositoryInterface $repo,
        private readonly SnippetTranslationRepositoryInterface $transRepo,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly AdminUrlGenerator $router,
        private readonly FlashNotifier $flashNotifier,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if ($this->authChecker->isGranted(UserRoles::ROLE_EDITOR) === false) {
            throw new AccessDeniedException('Access denied.');
        }

        $snippet = $this->repo->findById($id);
        if ($snippet === null) {
            throw new NotFoundHttpException('The snippet does not exist');
        }

        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        foreach ($snippet->getTranslations() as $trans) {
            $this->transRepo->remove($trans);
        }
        $this->repo->remove($snippet, true);
        $this->flashNotifier->changesSaved();


        return new RedirectResponse(
            $this->router->generate('admin_snippet_list')
        );
    }
}
