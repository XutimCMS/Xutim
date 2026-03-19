<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\NotificationBundle\Domain\Factory\NotificationFactory;
use Xutim\NotificationBundle\Entity\NotificationSeverity;
use Xutim\NotificationBundle\Message\Notification\SendNotificationMessage;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

final readonly class TranslatorNotificationService
{
    public function __construct(
        private TranslatorRecipientResolver $recipientResolver,
        private ReferenceTranslationLocaleResolver $referenceTranslationLocaleResolver,
        private NotificationRepository $notificationRepository,
        private MessageBusInterface $commandBus,
        private UrlGeneratorInterface $urlGenerator,
        private NotificationFactory $notificationFactory,
    ) {
    }

    /**
     * @param list<string> $locales
     */
    public function notifyNewTranslationLocales(
        ArticleInterface|PageInterface $content,
        array $locales,
        ?string $actorIdentifier = null,
        NotificationSeverity $severity = NotificationSeverity::Warning,
        ?string $customTitle = null,
        ?string $customMessage = null,
        bool $sendEmail = false,
        bool $deduplicate = true,
    ): void {
        if ($locales === []) {
            return;
        }

        $recipients = $this->recipientResolver->resolveForLocales($locales, $actorIdentifier);
        $contentType = $content instanceof ArticleInterface ? 'article' : 'page';
        $contentTitle = $content->getDefaultTranslation()->getTitle();
        $actionRoute = $this->buildContentRouteData($content);

        foreach ($recipients as $recipient) {
            foreach ($locales as $locale) {
                if (!$recipient->canTranslate($locale)) {
                    continue;
                }

                $deduplicationKey = sprintf('translation_missing:%s:%s:%s', $contentType, $content->getId()->toRfc4122(), $locale);
                if ($deduplicate && $this->notificationRepository->hasDeduplicatedNotification($recipient, $deduplicationKey)) {
                    continue;
                }

                $body = $customMessage ?? sprintf(
                    'A new %s translation is needed for "%s" in %s.',
                    $contentType,
                    $contentTitle,
                    strtoupper($locale)
                );
                $title = $customTitle ?? sprintf('New translation needed: %s', $contentTitle);

                $this->persistAndDispatch(
                    $recipient,
                    'translation_locale_added',
                    $severity,
                    $title,
                    $body,
                    $this->generateContentEditUrl($actionRoute['routeName'], $content, $locale),
                    'Open translation',
                    array_merge([
                        'contentType' => $contentType,
                        'contentId' => $content->getId()->toRfc4122(),
                        'locale' => $locale,
                    ], $this->buildActionPayload($actionRoute['routeName'], $content, $locale)),
                    $deduplicate ? $deduplicationKey : null,
                    $sendEmail || $severity->shouldEmailByDefault(),
                );
            }
        }
    }

    public function notifyReferenceTranslationChanged(
        ContentTranslationInterface $referenceTranslation,
        ?string $actorIdentifier = null,
    ): void {
        $content = $referenceTranslation->getObject();
        $referenceLocale = $referenceTranslation->getLocale();
        $staleLocales = $this->referenceTranslationLocaleResolver->resolveStaleLocales($referenceTranslation);

        if ($staleLocales === []) {
            return;
        }

        $recipients = $this->recipientResolver->resolveForLocales($staleLocales, $actorIdentifier);
        $contentType = $content instanceof ArticleInterface ? 'article' : 'page';
        $contentTitle = $content->getDefaultTranslation()->getTitle();
        $version = $referenceTranslation->getUpdatedAt()->format('YmdHis');
        $actionRoute = $this->buildContentRouteData($content);

        foreach ($recipients as $recipient) {
            foreach ($staleLocales as $locale) {
                if (!$recipient->canTranslate($locale)) {
                    continue;
                }

                $deduplicationKey = sprintf(
                    'reference_changed:%s:%s:%s:%s',
                    $contentType,
                    $content->getId()->toRfc4122(),
                    $locale,
                    $version
                );

                if ($this->notificationRepository->hasDeduplicatedNotification($recipient, $deduplicationKey)) {
                    continue;
                }

                $this->persistAndDispatch(
                    $recipient,
                    'translation_reference_changed',
                    NotificationSeverity::Warning,
                    sprintf('Source translation changed: %s', $contentTitle),
                    sprintf(
                        'The reference translation for "%s" changed. Please review the %s translation.',
                        $contentTitle,
                        strtoupper($locale)
                    ),
                    $this->generateContentEditUrl($actionRoute['routeName'], $content, $locale),
                    'Review translation',
                    array_merge([
                        'contentType' => $contentType,
                        'contentId' => $content->getId()->toRfc4122(),
                        'locale' => $locale,
                        'referenceLocale' => $referenceLocale,
                    ], $this->buildActionPayload($actionRoute['routeName'], $content, $locale)),
                    $deduplicationKey,
                    false,
                );
            }
        }
    }

    private function persistAndDispatch(
        UserInterface $recipient,
        string $type,
        NotificationSeverity $severity,
        string $title,
        string $body,
        ?string $actionUrl,
        ?string $actionLabel,
        array $payload,
        ?string $deduplicationKey,
        bool $sendEmail,
    ): void {
        // TODO: email delivery disabled
        $channels = ['database'];

        $notification = $this->notificationFactory->create(
            $recipient,
            $type,
            $severity,
            $title,
            $body,
            $actionUrl,
            $actionLabel,
            $channels,
            $payload,
            $deduplicationKey,
        );

        $this->notificationRepository->save($notification, true);

        if (in_array('email', $channels, true)) {
            $this->commandBus->dispatch(new SendNotificationMessage($notification->getId()));
        }
    }

    /**
     * @return array{routeName: string}
     */
    private function buildContentRouteData(ArticleInterface|PageInterface $content): array
    {
        return [
            'routeName' => $content instanceof ArticleInterface ? 'admin_article_edit' : 'admin_page_edit',
        ];
    }

    /**
     * @return array{routeName: string, routeParameters: array{id: string, _content_locale: string}}
     */
    private function buildActionPayload(string $routeName, ArticleInterface|PageInterface $content, string $locale): array
    {
        return [
            'routeName' => $routeName,
            'routeParameters' => [
                '_content_locale' => $locale,
                'id' => $content->getId()->toRfc4122(),
            ],
        ];
    }

    private function generateContentEditUrl(string $routeName, ArticleInterface|PageInterface $content, string $locale): string
    {
        return $this->urlGenerator->generate($routeName, [
            '_content_locale' => $locale,
            'id' => $content->getId(),
        ]);
    }
}
