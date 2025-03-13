<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Dto\Admin\Page\PageMinimalDto;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\PageDetailsType;
use Xutim\CoreBundle\Message\Command\Page\EditPageDetailsCommand;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/page/details-edit/{id}', name: 'admin_page_details_edit')]
class EditPageDetailsAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage
    ) {
    }

    public function __invoke(Request $request, Page $page): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(PageDetailsType::class, PageMinimalDto::fromPage($page), [
            'action' => $this->generateUrl('admin_page_details_edit', ['id' => $page->getId()]),
            'page' => $page
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PageMinimalDto $dto */
            $dto = $form->getData();
            $command = EditPageDetailsCommand::fromDto(
                $dto,
                $page->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier()
            );
            $this->commandBus->dispatch($command);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->renderBlock('@XutimCore/admin/page/page_edit_details.html.twig', 'success_stream', [
                    'page' => $page
                ]);
            }

            $fallbackUrl = $this->generateUrl('admin_page_edit', [
                'id' => $page->getId()
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimCore/admin/page/page_edit_details.html.twig', [
            'form' => $form,
            'page' => $page
        ]);
    }
}
