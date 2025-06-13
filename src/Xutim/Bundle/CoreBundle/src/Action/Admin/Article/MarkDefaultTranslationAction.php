<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use App\Entity\Core\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Event\Article\ArticleDefaultTranslationUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Security\UserStorage;
use Xutim\CoreBundle\Service\CsrfTokenChecker;

#[Route('/article/{id}/mark-default-translation/{transId}', name: 'admin_article_mark_default_translation')]
class MarkDefaultTranslationAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly CsrfTokenChecker $csrfTokenChecker,
        private readonly ArticleRepository $articleRepo,
        private readonly ContentTranslationRepository $contentTranslationRepo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository
    ) {
    }

    public function __invoke(Request $request, string $id, string $transId): Response
    {
        $article = $this->articleRepo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }

        $trans = $this->contentTranslationRepo->find($transId);
        if ($trans === null) {
            throw $this->createNotFoundException('The content translation does not exist');
        }

        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $this->csrfTokenChecker->checkTokenFromFormRequest('pulse-dialog', $request);
        $article->setDefaultTranslation($trans);
        $this->articleRepo->save($article, true);

        $event = new ArticleDefaultTranslationUpdatedEvent($article->getId(), $trans->getId());
        $logEntry = $this->logEventFactory->create(
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
