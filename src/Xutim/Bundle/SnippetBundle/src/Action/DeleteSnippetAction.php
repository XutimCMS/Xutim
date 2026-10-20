<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xutim\CoreBundle\Repository\BlockItemRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Security\CsrfTokenChecker;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
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
        private readonly BlockItemRepository $blockItemRepo,
        private readonly TranslatorInterface $translator,
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

        $this->csrfTokenChecker->checkTokenFromFormRequest('xutim-dialog', $request);

        $blockCodes = $this->blockCodesUsing($snippet);
        if ($blockCodes !== []) {
            $this->flashNotifier->error($this->translator->trans(
                'The snippet is still used by the block %blocks%. Remove it from the block first.',
                ['%blocks%' => implode(', ', $blockCodes)],
                'admin'
            ));

            return new RedirectResponse($this->router->generate('admin_snippet_list'));
        }

        foreach ($snippet->getTranslations() as $trans) {
            $this->transRepo->remove($trans);
        }
        $this->repo->remove($snippet, true);
        $this->flashNotifier->changesSaved();

        return new RedirectResponse(
            $this->router->generate('admin_snippet_list')
        );
    }

    /**
     * @return list<string>
     */
    private function blockCodesUsing(SnippetInterface $snippet): array
    {
        $codes = [];
        foreach ($this->blockItemRepo->findBySnippet($snippet) as $item) {
            $codes[] = $item->getBlock()->getCode();
        }

        return array_values(array_unique($codes));
    }
}
