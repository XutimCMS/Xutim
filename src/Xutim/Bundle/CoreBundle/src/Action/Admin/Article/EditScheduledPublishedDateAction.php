<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Event\Article\ArticleScheduledDateUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Form\Admin\PublishedDateType;
use Xutim\CoreBundle\Message\Command\PublicationStatus\ChangePublicationStatusCommand;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SecurityBundle\Service\UserStorage;

class EditScheduledPublishedDateAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly ArticleRepository $repo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository,
        private readonly MessageBusInterface $commandBus,
        private readonly ContentContext $contentContext,
        private readonly AdminUrlGenerator $router,
        private readonly TranslatorAuthChecker $transAuthChecker
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
        $this->transAuthChecker->denyUnlessCanTranslate($translation->getLocale());
        $isEditor = $this->isGranted('ROLE_EDITOR');
        $bulkCount = $isEditor ? $article->getTranslations()->count() : 1;
        $form = $this->createForm(PublishedDateType::class, ['publishedAt' => $article->getScheduledAt()], [
            'action' => $this->router->generate('admin_article_edit_scheduled_publication_date', ['id' => $article->getId()]),
            'future_date_only' => true,
            'disable_date' => $isEditor === false,
            'bulk_count' => $bulkCount,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{publishedAt: \DateTimeImmutable, applyToAll?: bool} $data */
            $data = $form->getData();
            $applyToAll = ($data['applyToAll'] ?? false) === true;

            $article->setScheduledAt($data['publishedAt']);
            $this->repo->save($article, true);

            $event = new ArticleScheduledDateUpdatedEvent($article->getId(), $data['publishedAt']);
            $logEntry = $this->logEventFactory->create(
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Article::class,
                $event
            );

            $user = $this->userStorage->getUserWithException();
            $targets = $applyToAll ? $article->getTranslations()->toArray() : [$translation];
            if ($applyToAll === true) {
                $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
                foreach ($targets as $target) {
                    $this->transAuthChecker->denyUnlessCanTranslate($target->getLocale());
                }
            }
            foreach ($targets as $target) {
                $this->commandBus->dispatch(new ChangePublicationStatusCommand(
                    $target->getId(),
                    PublicationStatus::Scheduled,
                    $user->getUserIdentifier()
                ));
            }
            $this->eventRepository->save($logEntry, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_publication_date.html.twig', 'stream_success', [
                    'article' => $article,
                    'translation' => $translation
                ]);

                $this->addFlash('stream', $stream);
            }
            
            $fallbackUrl = $this->router->generate('admin_article_edit', [
                'id' => $article->getId()
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimCore/admin/article/article_edit_publication_date.html.twig', [
            'form' => $form
        ]);
    }
}
