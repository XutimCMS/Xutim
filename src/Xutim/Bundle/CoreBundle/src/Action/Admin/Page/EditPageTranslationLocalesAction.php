<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Dto\Admin\Page\PageTranslationLocalesDto;
use Xutim\CoreBundle\Form\Admin\PageTranslationLocalesType;
use Xutim\CoreBundle\Message\Command\Page\EditPageTranslationLocalesCommand;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class EditPageTranslationLocalesAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly PageRepository $pageRepo,
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

        $existingTranslationLocales = $page->getTranslations()->map(fn ($t) => $t->getLocale())->toArray();
        $form = $this->createForm(PageTranslationLocalesType::class, PageTranslationLocalesDto::fromPage($page), [
            'action' => $this->router->generate('admin_page_translation_locales_edit', ['id' => $page->getId()]),
            'existing_translation_locales' => array_values($existingTranslationLocales),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PageTranslationLocalesDto $dto */
            $dto = $form->getData();
            $command = EditPageTranslationLocalesCommand::fromDto(
                $dto,
                $page->getId(),
                $this->userStorage->getUserWithException()->getUserIdentifier(),
            );
            $this->commandBus->dispatch($command);

            $this->addFlash('success', 'flash.changes_made_successfully');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $stream = $this->renderBlockView('@XutimCore/admin/page/page_edit_translation_locales.html.twig', 'stream_success', [
                    'page' => $page,
                ]);
                $this->addFlash('stream', $stream);
            }

            $fallbackUrl = $this->router->generate('admin_page_edit', [
                'id' => $page->getId(),
            ]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl), 302);
        }

        return $this->render('@XutimCore/admin/page/page_edit_translation_locales.html.twig', [
            'form' => $form,
            'page' => $page,
        ]);
    }
}
