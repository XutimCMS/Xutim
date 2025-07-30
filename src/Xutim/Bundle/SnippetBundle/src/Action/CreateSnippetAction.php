<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Action;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\FlashNotifier;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SnippetBundle\Context\SnippetsContext;
use Xutim\SnippetBundle\Domain\Factory\SnippetFactoryInterface;
use Xutim\SnippetBundle\Domain\Factory\SnippetTranslationFactoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetTranslationRepositoryInterface;
use Xutim\SnippetBundle\Form\SnippetFormData;
use Xutim\SnippetBundle\Form\SnippetType;

class CreateSnippetAction
{
    public function __construct(
        private readonly SnippetRepositoryInterface $repo,
        private readonly SnippetTranslationRepositoryInterface $transRepo,
        private readonly ContentContext $context,
        private readonly SnippetsContext $snippetsContext,
        private readonly BlockContext $blockContext,
        private readonly SiteContext $siteContext,
        private readonly SnippetFactoryInterface $snippetFactory,
        private readonly SnippetTranslationFactoryInterface $snippetTransFactory,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly AdminUrlGenerator $router,
        private readonly FlashNotifier $flashNotifier,
        private readonly string $snippetVersionPath,
    ) {
    }


    public function __invoke(Request $request): Response
    {
        if ($this->authChecker->isGranted(UserRoles::ROLE_EDITOR) === false) {
            throw new AccessDeniedException('Access denied.');
        }
        $form = $this->formFactory->create(SnippetType::class, null, [
            'action' => $this->router->generate('admin_snippet_new')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SnippetFormData $data */
            $data = $form->getData();
            $locale = $this->context->getLanguage();
            $snippet = $this->snippetFactory->create($data->getCode(), $data->getDescription(), $data->getCategory());
            foreach ($data->getContents() as $contentLocale => $content) {
                $trans = $this->snippetTransFactory->create($snippet, $contentLocale, $content);
                $this->transRepo->save($trans);
            }

            $this->repo->save($snippet, true);
            $this->snippetsContext->resetSnippet($snippet->getCode());
            $this->blockContext->resetBlocksBelongsToSnippet($snippet);
            $this->siteContext->resetMenu();
            if ($snippet->isRouteType() === true) {
                // Restart the snippet_routes router cache. See
                // CustomRouteLoader for more information
                file_put_contents($this->snippetVersionPath, microtime());
            }

            $this->flashNotifier->changesSaved();

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->twig
                    ->load('@XutimSnippet/admin/snippet/new.html.twig')
                    ->renderBlock('stream_success');

                $this->flashNotifier->stream($stream);
            }

            return new RedirectResponse(
                $this->router->generate('admin_snippet_list', [], Response::HTTP_SEE_OTHER)
            );
        }

        $html = $this->twig->render('@XutimSnippet/admin/snippet/new.html.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($html);
    }
}
