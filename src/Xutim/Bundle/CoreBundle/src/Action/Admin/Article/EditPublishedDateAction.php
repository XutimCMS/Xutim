<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\Article\ArticlePublicationDateUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Form\Admin\PublishedDateType;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangePublicationStatusCommand;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/article/edit-publication-date/{id}', name: 'admin_article_edit_publication_date', methods: ['get', 'post'])]
class EditPublishedDateAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly ArticleRepository $repo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository,
        private readonly BlockContext $blockContext,
        private readonly MessageBusInterface $commandBus,
        private readonly ContentContext $contentContext
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $article = $this->repo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        $translation = $article->getTranslationByLocale($this->contentContext->getLanguage());
        if ($translation === null) {
            throw $this->createNotFoundException('The translation of an article does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
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
            $logEntry = $this->logEventFactory->create(
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Article::class,
                $event
            );

            $user = $this->userStorage->getUserWithException();
            $command = new ChangePublicationStatusCommand(
                $translation->getId(),
                PublicationStatus::Scheduled,
                $user->getUserIdentifier()
            );
            $this->commandBus->dispatch($command);
            $this->eventRepository->save($logEntry, true);

            $this->blockContext->resetBlocksBelongsToArticle($article);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_publication_date.html.twig', 'stream_success', [
                    'article' => $article,
                    'translation' => $translation
                ]);

                $this->addFlash('stream', $stream);
            }
            
            $fallbackUrl = $this->generateUrl('admin_article_edit', [
                'id' => $article->getId()
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimCore/admin/article/article_edit_publication_date.html.twig', [
            'form' => $form
        ]);
    }
}
