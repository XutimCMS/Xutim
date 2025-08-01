<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\Article\ArticleTranslationPublicationDateUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Form\Admin\PublishedDateType;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class EditPublishedDateAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly ContentTranslationRepository $repo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository,
        private readonly BlockContext $blockContext,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $trans = $this->repo->find($id);
        if ($trans === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $article = $trans->getArticle();
        $form = $this->createForm(PublishedDateType::class, ['publishedAt' => $trans->getPublishedAt()], [
            'action' => $this->router->generate('admin_article_edit_publication_date', ['id' => $trans->getId()]),
            'future_date_only' => false
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{publishedAt: \DateTimeImmutable} $data */
            $data = $form->getData();

            $trans->changePublishedAt($data['publishedAt']);
            $this->repo->save($trans, true);


            $event = new ArticleTranslationPublicationDateUpdatedEvent($trans->getId(), $data['publishedAt']);
            $logEntry = $this->logEventFactory->create(
                $trans->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                ContentTranslation::class,
                $event
            );

            $this->eventRepository->save($logEntry, true);


            $this->blockContext->resetBlocksBelongsToArticle($trans->getArticle());

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_publication_date.html.twig', 'stream_success', [
                    'article' => $article,
                    'translation' => $trans
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
