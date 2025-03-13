<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Event\Article\ArticleDefaultTranslationUpdatedEvent;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/article/{id}/mark-default-translation/{transId}', name: 'admin_article_mark_default_translation')]
class MarkDefaultTranslationAction extends AbstractController
{
    public function __construct(
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly ArticleRepository $repository,
        private readonly UserStorage $userStorage,
        private readonly EventRepository $eventRepository
    ) {
    }

    public function __invoke(
        Request $request,
        Article $article,
        #[MapEntity(mapping: ['transId' => 'id'])]
        ContentTranslation $trans
    ): Response {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $article->setDefaultTranslation($trans);
        $this->repository->save($article, true);

        $event = new ArticleDefaultTranslationUpdatedEvent($article->getId(), $trans->getId());
        $logEntry = new Event(
            $article->getId(),
            $this->userStorage->getUserWithException()->getUserIdentifier(),
            Article::class,
            $event
        );
        $this->eventRepository->save($logEntry, true);
        $this->addFlash('success', 'flash.changes_made_successfully');

        return $this->redirect($request->headers->get('referer', ''));
    }
}
