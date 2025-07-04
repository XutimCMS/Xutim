<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Snippet;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Context\SnippetsContext;
use Xutim\CoreBundle\Domain\Factory\SnippetFactory;
use Xutim\CoreBundle\Domain\Factory\SnippetTranslationFactory;
use Xutim\CoreBundle\Form\Admin\Dto\SnippetDto;
use Xutim\CoreBundle\Form\Admin\SnippetType;
use Xutim\CoreBundle\Repository\SnippetRepository;
use Xutim\CoreBundle\Repository\SnippetTranslationRepository;
use Xutim\SecurityBundle\Security\UserRoles;

class CreateSnippetAction extends AbstractController
{
    public function __construct(
        private readonly SnippetRepository $repo,
        private readonly SnippetTranslationRepository $transRepo,
        private readonly ContentContext $context,
        private readonly SnippetsContext $snippetsContext,
        private readonly BlockContext $blockContext,
        private readonly SiteContext $siteContext,
        private readonly SnippetFactory $snippetFactory,
        private readonly SnippetTranslationFactory $snippetTransFactory,
        private readonly string $snippetVersionPath,
    ) {
    }

    #[Route('/snippet/new', name: 'admin_snippet_new', methods: ['get', 'post'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(SnippetType::class, null, [
            'action' => $this->generateUrl('admin_snippet_new')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SnippetDto $dto */
            $dto = $form->getData();
            $locale = $this->context->getLanguage();
            $snippet = $this->snippetFactory->create($dto->code);
            foreach ($dto->contents as $contentLocale => $content) {
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

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/snippet/new.html.twig', 'stream_success');

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_snippet_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/snippet/new.html.twig', [
            'form' => $form,
        ]);
    }
}
