<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Snippet;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SnippetsContext;
use Xutim\CoreBundle\Entity\Snippet;
use Xutim\CoreBundle\Entity\SnippetTranslation;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\Dto\SnippetDto;
use Xutim\CoreBundle\Form\Admin\SnippetType;
use Xutim\CoreBundle\Repository\SnippetRepository;
use Xutim\CoreBundle\Repository\SnippetTranslationRepository;

class CreateSnippetAction extends AbstractController
{
    public function __construct(
        private readonly SnippetRepository $repo,
        private readonly SnippetTranslationRepository $transRepo,
        private readonly ContentContext $context,
        private readonly SnippetsContext $snippetsContext
    ) {
    }

    #[Route('/snippet/new', name: 'admin_snippet_new', methods: ['get', 'post'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(SnippetType::class, null, [
            'action' => $this->generateUrl('admin_snippet_new')
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SnippetDto $dto */
            $dto = $form->getData();
            $locale = $this->context->getLanguage();
            $snippet = new Snippet($dto->code);
            foreach ($dto->contents as $contentLocale => $content) {
                $trans = new SnippetTranslation($snippet, $contentLocale, $content);
                $this->transRepo->save($trans);
            }

            $this->repo->save($snippet, true);
            $this->snippetsContext->resetSnippet($snippet->getCode());

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
