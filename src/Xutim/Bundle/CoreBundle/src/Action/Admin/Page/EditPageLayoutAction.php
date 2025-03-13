<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Domain\Event\Page\PageLayoutUpdatedEvent;
use Xutim\CoreBundle\Entity\Event;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\PageLayoutType;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\EventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/page/layout-edit/{id}', name: 'admin_page_layout_edit')]
class EditPageLayoutAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepo,
        private readonly LayoutLoader $layoutLoader,
        private readonly UserStorage $userStorage,
        private readonly EventRepository $eventRepository
    ) {
    }

    public function __invoke(Request $request, Page $page): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $layout = $this->layoutLoader->getArticleLayoutByCode($page->getLayout());
        $form = $this->createForm(PageLayoutType::class, ['layout' => $layout], [
            'action' => $this->generateUrl('admin_page_layout_edit', ['id' => $page->getId()]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $page->changeLayout($data['layout']);
            $this->pageRepo->save($page, true);

            $event = new PageLayoutUpdatedEvent($page->getId(), $data['layout']?->code);
            $logEntry = new Event(
                $page->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                Page::class,
                $event
            );
            $this->eventRepository->save($logEntry, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if ($request->headers->has('turbo-frame')) {
                $stream = $this->renderBlockView('@XutimCore/admin/page/page_edit_layout.html.twig', 'stream_success', [
                    'page' => $page
                ]);

                $this->addFlash('stream', $stream);
            }

            $fallbackUrl = $this->generateUrl('admin_page_edit', [
                'id' => $page->getId()
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimCore/admin/page/page_edit_layout.html.twig', [
            'form' => $form,
            'page' => $page
        ]);
    }
}
