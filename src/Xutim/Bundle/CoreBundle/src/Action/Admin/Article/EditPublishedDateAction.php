<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Event\Article\ArticlePublicationDateUpdatedEvent;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\PublishedDateType;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/article/edit-publication-date/{id?null}', name: 'admin_article_edit_publication_date', methods: ['get', 'post'])]
class EditPublishedDateAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $repo,
        private readonly UserStorage $userStorage,
        private readonly EventRepository $eventRepository
    ) {
    }

    public function __invoke(Request $request, Article $article): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(PublishedDateType::class, ['publishedAt' => $article->getPublishedAt()], [
            'action' => $this->generateUrl('admin_article_edit_publication_date', ['id' => $article->getId()])
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{publishedAt: \DateTimeImmutable} $data */
            $data = $form->getData();

            $article->setPublishedAt($data['publishedAt']);
            $this->repo->save($article, true);

            $event = new ArticlePublicationDateUpdatedEvent($article->getId(), $data['publishedAt']);
            $logEntry = new Event(
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Article::class,
                $event
            );
            $this->eventRepository->save($logEntry, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_publication_date.html.twig', 'stream_success', [
                    'article' => $article
                ]);

                $this->addFlash('stream', $stream);
            }
            
            // todo: referer - can be show or edit page.
            return $this->redirectToRoute('admin_article_edit', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('@XutimCore/admin/article/article_edit_publication_date.html.twig', [
            'form' => $form
        ]);
    }
}
