<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
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
            $recipientLocales = $recipient->getTranslationLocales();
            foreach ($locales as $locale) {
                if (!in_array($locale, $recipientLocales, true)) {
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

    /**
     * @param array<string, mixed> $payload
     */
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
        /** @var list<string> $channels */
        $channels = ['database'];
        if ($sendEmail) {
            $channels[] = 'email';
        }

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
