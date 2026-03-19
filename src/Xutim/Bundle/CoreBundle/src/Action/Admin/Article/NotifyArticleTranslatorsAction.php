<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\NotificationBundle\Dto\Admin\Notification\NotificationAlertDto;
use Xutim\NotificationBundle\Form\Admin\NotificationAlertType;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\CoreBundle\Service\TranslatorNotificationService;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class NotifyArticleTranslatorsAction extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly TranslatorNotificationService $translatorNotificationService,
        private readonly UserStorage $userStorage,
        private readonly AdminUrlGenerator $router,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);

        $article = $this->articleRepository->find($id);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist.');
        }

        $allowedLocales = $article->hasAllTranslationLocales()
            ? $this->siteContext->getAllLocales()
            : $article->getTranslationLocales();

        $mainLocales = array_values(array_intersect($this->siteContext->getLocales(), $allowedLocales));
        $extendedLocales = array_values(array_intersect($this->siteContext->getExtendedContentLocales(), $allowedLocales));

        $form = $this->createForm(NotificationAlertType::class, new NotificationAlertDto(
            locales: $mainLocales,
            title: sprintf('Translation needed: %s', $article->getDefaultTranslation()->getTitle()),
        ), [
            'main_locales' => $mainLocales,
            'extended_locales' => $extendedLocales,
            'action' => $this->router->generate('admin_article_notify_translators', ['id' => $article->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var NotificationAlertDto $data */
            $data = $form->getData();
            $this->translatorNotificationService->notifyNewTranslationLocales(
                $article,
                $data->locales,
                $this->userStorage->getUserWithException()->getUserIdentifier(),
                $data->severity,
                $data->title !== '' ? $data->title : null,
                $data->message !== '' ? $data->message : null,
                $data->sendEmail,
                false,
            );

            $this->addFlash('success', 'Translators have been notified.');

            $fallbackUrl = $this->router->generate('admin_article_edit', ['id' => $article->getId()]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimNotification/admin/notification/notify_translators.html.twig', [
            'form' => $form,
            'entity' => $article,
            'entityType' => 'article',
        ]);
    }
}
