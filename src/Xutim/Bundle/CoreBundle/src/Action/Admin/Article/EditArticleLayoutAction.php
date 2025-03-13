<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Domain\Event\Article\ArticleLayoutUpdatedEvent;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\ArticleLayoutType;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/article/layout-edit/{id}', name: 'admin_article_layout_edit')]
class EditArticleLayoutAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly LayoutLoader $layoutLoader,
        private readonly UserStorage $userStorage,
        private readonly EventRepository $eventRepository
    ) {
    }

    public function __invoke(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $layout = $this->layoutLoader->getArticleLayoutByCode($article->getLayout());
        $form = $this->createForm(ArticleLayoutType::class, ['layout' => $layout], [
            'action' => $this->generateUrl('admin_article_layout_edit', ['id' => $article->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{layout: ?Layout} $data */
            $data = $form->getData();

            $article->changeLayout($data['layout']);
            $this->articleRepository->save($article, true);

            $event = new ArticleLayoutUpdatedEvent($article->getId(), $data['layout']?->code);
            $logEntry = new Event(
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Article::class,
                $event
            );
            $this->eventRepository->save($logEntry, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_layout.html.twig', 'stream_success', [
                    'article' => $article
                ]);

                $this->addFlash('stream', $stream);
            }

            return $this->redirectToRoute('admin_article_edit', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('@XutimCore/admin/article/article_edit_layout.html.twig', [
            'form' => $form,
            'article' => $article
        ]);
    }
}
