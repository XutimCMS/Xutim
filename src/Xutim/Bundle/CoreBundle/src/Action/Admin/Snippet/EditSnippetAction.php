<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Snippet;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Context\SnippetsContext;
use Xutim\CoreBundle\Domain\Factory\SnippetTranslationFactory;
use Xutim\CoreBundle\Form\Admin\Dto\SnippetDto;
use Xutim\CoreBundle\Form\Admin\SnippetType;
use Xutim\CoreBundle\Repository\SnippetRepository;
use Xutim\CoreBundle\Repository\SnippetTranslationRepository;
use Xutim\CoreBundle\Security\TranslatorAuthChecker;

#[Route('/snippet/edit/{id}', name: 'admin_snippet_edit', methods: ['get', 'post'])]
class EditSnippetAction extends AbstractController
{
    public function __construct(
        private readonly SnippetTranslationRepository $translationRepo,
        private readonly SnippetRepository $snippetRepo,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentContext $context,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly SnippetsContext $snippetsContext,
        private readonly BlockContext $blockContext,
        private readonly SiteContext $siteContext,
        private readonly SnippetTranslationFactory $snippetTransFactory,
        private readonly string $snippetVersionPath,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $snippet = $this->snippetRepo->find($id);
        if ($snippet === null) {
            throw $this->createNotFoundException('The snippet does not exist');
        }
        $locale = $this->context->getLanguage();
        $form = $this->createForm(SnippetType::class, $snippet->toDto(), [
            'disabled' => $this->transAuthChecker->canTranslate($locale) === false,
            'action' => $this->generateUrl('admin_snippet_edit', ['id' => $snippet->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->transAuthChecker->denyUnlessCanTranslate($locale);
            /** @var SnippetDto $data */
            $data = $form->getData();
            $snippet->change($data->code);

            foreach ($data->contents as $contentLocale => $content) {
                $trans = $snippet->getTranslationByLocale($contentLocale);
                if ($trans === null) {
                    if ($content === '') {
                        continue;
                    }
                    $trans = $this->snippetTransFactory->create($snippet, $contentLocale, $content);
                    $snippet->addTranslation($trans);
                    $this->translationRepo->save($trans);
                    continue;
                }

                $trans->update($content);
            }

            $this->entityManager->flush();
            $this->snippetsContext->resetSnippet($snippet->getCode());
            $this->blockContext->resetBlocksBelongsToSnippet($snippet);
            $this->siteContext->resetMenu();
            if ($snippet->isRouteType() === true) {
                // Restart the snippet_routes router cache. See
                // CustomRouteLoader for more information
                file_put_contents($this->snippetVersionPath, microtime());
            }
            $this->addFlash('success', 'Changes were made successfully.');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/snippet/edit.html.twig', 'stream_success');
            
                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_snippet_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/snippet/edit.html.twig', [
            'snippet' => $snippet,
            'form' => $form
        ]);
    }
}
