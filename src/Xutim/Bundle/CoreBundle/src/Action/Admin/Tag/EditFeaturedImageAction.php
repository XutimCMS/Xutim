<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\Article\ArticleFeaturedImageUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Form\Admin\Dto\ImageDto;
use Xutim\CoreBundle\Form\Admin\FeaturedImageType;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class EditFeaturedImageAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly ArticleRepository $repo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository,
        private readonly MediaRepositoryInterface $mediaRepo,
        private readonly BlockContext $blockContext,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $article = $this->repo->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(FeaturedImageType::class, new ImageDto($article->getFeaturedImage()?->id()), [
            'action' => $this->router->generate('admin_article_featured_image_edit', ['id' => $article->getId()])
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ImageDto $data */
            $data = $form->getData();

            $file = $data->id === null ? null : $this->mediaRepo->findById($data->id);
            $article->changeFeaturedImage($file);
            $this->repo->save($article, true);

            $event = new ArticleFeaturedImageUpdatedEvent($article->getId(), $data->id);
            $logEntry = $this->logEventFactory->create(
                $article->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Article::class,
                $event
            );
            $this->eventRepository->save($logEntry, true);
            $this->blockContext->resetBlocksBelongsToArticle($article);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/article/article_edit_featured_image.html.twig', 'stream_success', [
                    'article' => $article
                ]);

                $this->addFlash('stream', $stream);
            }

            $fallbackUrl = $this->router->generate('admin_article_edit', [
                'id' => $article->getId()
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimCore/admin/article/article_edit_featured_image.html.twig', [
            'form' => $form
        ]);
    }
}
