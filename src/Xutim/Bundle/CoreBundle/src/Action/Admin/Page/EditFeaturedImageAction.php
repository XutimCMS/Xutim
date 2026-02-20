<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\BlockContext;
use Xutim\CoreBundle\Domain\Event\Page\PageFeaturedImageUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Form\Admin\Dto\ImageDto;
use Xutim\CoreBundle\Form\Admin\FeaturedImageType;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class EditFeaturedImageAction extends AbstractController
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly PageRepository $pageRepo,
        private readonly UserStorage $userStorage,
        private readonly LogEventRepository $eventRepository,
        private readonly MediaRepositoryInterface $mediaRepo,
        private readonly BlockContext $blockContext,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $page = $this->pageRepo->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist');
        }
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(FeaturedImageType::class, new ImageDto($page->getFeaturedImage()?->id()), [
            'action' => $this->router->generate('admin_page_featured_image_edit', ['id' => $page->getId()]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ImageDto $data */
            $data = $form->getData();
            
            $page->changeFeaturedImage($data->id === null ? null : $this->mediaRepo->findById($data->id));
            $this->pageRepo->save($page, true);
            $this->blockContext->resetBlocksBelongsToPage($page);

            $event = new PageFeaturedImageUpdatedEvent($page->getId(), $data->id);
            $logEntry = $this->logEventFactory->create(
                $page->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Page::class,
                $event
            );
            $this->eventRepository->save($logEntry, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/page/page_edit_featured_image.html.twig', 'stream_success', [
                    'page' => $page
                ]);

                $this->addFlash('stream', $stream);
            }

            $fallbackUrl = $this->router->generate('admin_page_edit', [
                'id' => $page->getId()
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimCore/admin/page/page_edit_featured_image.html.twig', [
            'form' => $form,
            'page' => $page
        ]);
    }
}
