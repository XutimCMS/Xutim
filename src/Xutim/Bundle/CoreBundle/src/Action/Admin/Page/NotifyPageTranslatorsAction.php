<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\NotificationBundle\Dto\Admin\Notification\NotificationAlertDto;
use Xutim\NotificationBundle\Form\Admin\NotificationAlertType;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\TranslatorNotificationService;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class NotifyPageTranslatorsAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly TranslatorNotificationService $translatorNotificationService,
        private readonly UserStorage $userStorage,
        private readonly AdminUrlGenerator $router,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);

        $page = $this->pageRepository->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('The page does not exist.');
        }

        $allowedLocales = $page->hasAllTranslationLocales()
            ? $this->siteContext->getAllLocales()
            : $page->getTranslationLocales();

        $mainLocales = array_values(array_intersect($this->siteContext->getLocales(), $allowedLocales));
        $extendedLocales = array_values(array_intersect($this->siteContext->getExtendedContentLocales(), $allowedLocales));

        $form = $this->createForm(NotificationAlertType::class, new NotificationAlertDto(
            locales: $mainLocales,
            title: sprintf('Translation needed: %s', $page->getDefaultTranslation()->getTitle()),
        ), [
            'main_locales' => $mainLocales,
            'extended_locales' => $extendedLocales,
            'action' => $this->router->generate('admin_page_notify_translators', ['id' => $page->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var NotificationAlertDto $data */
            $data = $form->getData();
            $this->translatorNotificationService->notifyNewTranslationLocales(
                $page,
                $data->locales,
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $data->severity,
                $data->title !== '' ? $data->title : null,
                $data->message !== '' ? $data->message : null,
                $data->sendEmail,
                false,
            );

            $this->addFlash('success', 'Translators have been notified.');

            $fallbackUrl = $this->router->generate('admin_page_edit', ['id' => $page->getId()]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimNotification/admin/notification/notify_translators.html.twig', [
            'form' => $form,
            'entity' => $page,
            'entityType' => 'page',
        ]);
    }
}
