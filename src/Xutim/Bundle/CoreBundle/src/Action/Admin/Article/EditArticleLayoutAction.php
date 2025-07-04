<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Domain\Event\Article\ArticleLayoutUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Form\Admin\ArticleLayoutType;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

#[Route('/article/layout-edit/{id}', name: 'admin_article_layout_edit')]
class EditArticleLayoutAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly ArticleRepository $articleRepository,
        private readonly LayoutLoader $layoutLoader,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $article = $this->articleRepository->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
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
            $logEntry = $this->logEventFactory->create(
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Article::class,
                $event
            );
            $this->eventRepository->save($logEntry, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_layout.html.twig', 'stream_success', [
                    'article' => $article
                ]);
                $this->addFlash('stream', $stream);
            }

            $fallbackUrl = $this->generateUrl('admin_article_edit', [
                'id' => $article->getId()
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimCore/admin/article/article_edit_layout.html.twig', [
            'form' => $form,
            'article' => $article
        ]);
    }
}
