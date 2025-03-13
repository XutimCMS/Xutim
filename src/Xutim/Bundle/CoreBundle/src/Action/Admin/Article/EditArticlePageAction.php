<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Event\Article\ArticlePageUpdatedEvent;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\ArticleInPageType;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/article/page-edit/{id}', name: 'admin_article_page_edit')]
class EditArticlePageAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly UserStorage $userStorage,
        private readonly EventRepository $eventRepository
    ) {
    }

    public function __invoke(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(ArticleInPageType::class, $article->getPage(), [
            'action' => $this->generateUrl('admin_article_page_edit', ['id' => $article->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Page $page */
            $page = $form->getData();

            if ($article->getPage()->getId()->equals($page->getId()) === false) {
                $article->change($page);
                $this->articleRepository->save($article, true);
            }

            $event = new ArticlePageUpdatedEvent($article->getId(), $page->getId());
            $logEntry = new Event(
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Article::class,
                $event
            );
            $this->eventRepository->save($logEntry, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_page.html.twig', 'stream_success', [
                    'article' => $article
                ]);

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_article_edit', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('@XutimCore/admin/article/article_edit_page.html.twig', [
            'form' => $form,
            'article' => $article
        ]);
    }
}
