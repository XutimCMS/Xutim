<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Snippet;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SnippetsContext;
use Xutim\CoreBundle\Entity\Snippet;
use Xutim\CoreBundle\Entity\SnippetTranslation;
use Xutim\CoreBundle\Form\Admin\Dto\SnippetDto;
use Xutim\CoreBundle\Form\Admin\SnippetType;
use Xutim\CoreBundle\Repository\SnippetTranslationRepository;
use Xutim\CoreBundle\Security\TranslatorAuthChecker;

#[Route('/snippet/edit/{id}', name: 'admin_snippet_edit', methods: ['get', 'post'])]
class EditSnippetAction extends AbstractController
{
    public function __construct(
        private readonly SnippetTranslationRepository $translationRepo,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentContext $context,
        private readonly TranslatorAuthChecker $transAuthChecker,
        private readonly SnippetsContext $snippetsContext
    ) {
    }

    public function __invoke(Request $request, Snippet $snippet): Response
    {
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

            foreach ($data->contents as $contentLocale => $content) {
                $trans = $snippet->getTranslationByLocale($contentLocale);
                if ($trans === null) {
                    if ($content === '') {
                        continue;
                    }
                    $trans = new SnippetTranslation($snippet, $contentLocale, $content);
                    $snippet->addTranslation($trans);
                    $this->translationRepo->save($trans);
                    continue;
                }

                $trans->update($content);
            }

            $this->entityManager->flush();
            $this->snippetsContext->resetSnippet($snippet->getCode());
            $this->addFlash('success', 'Changes were made successfully.');

            // if ($request->headers->has('turbo-frame')) {
            //     $stream = $this->renderBlockView('@XutimCore/admin/snippet/edit.html.twig', 'stream_success');
            //
            //     $this->addFlash('stream', $stream);
            // }

            return $this->redirectToRoute('admin_snippet_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('@XutimCore/admin/snippet/edit.html.twig', [
            'snippet' => $snippet,
            'form' => $form
        ]);
    }
}
