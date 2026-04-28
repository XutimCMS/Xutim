<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Tests\Application;

use RuntimeException;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\NotificationBundle\EventSubscriber\FailedMessageSubscriber;
use Xutim\NotificationBundle\Repository\NotificationRepository;

final class FailedMessageSubscriberTest extends KernelTestCase
{
    private FailedMessageSubscriber $subscriber;
    private NotificationRepository $notificationRepository;
    private SiteRepository $siteRepository;
    private SiteContext $siteContext;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->subscriber = $container->get(FailedMessageSubscriber::class);
        $this->notificationRepository = $container->get(NotificationRepository::class);
        $this->siteRepository = $container->get(SiteRepository::class);
        $this->siteContext = $container->get(SiteContext::class);
    }

    public function testEmitsAdminAlertOnFailure(): void
    {
        $this->configureAdminEmails(['ops@example.com']);

        $envelope = new Envelope(new stdClass());
        $event = new WorkerMessageFailedEvent($envelope, 'async', new RuntimeException('kaboom'));

        $this->subscriber->onMessageFailed($event);

        $notifications = $this->fetchAllAlertNotifications();
        $this->assertCount(1, $notifications);
        $notification = $notifications[0];
        $this->assertSame('failed_job', $notification->getType());
        $this->assertStringContainsString('stdClass', $notification->getTitle());
        $this->assertSame('stdClass', $notification->getPayload()['messageClass']);
        $this->assertSame(RuntimeException::class, $notification->getPayload()['exceptionClass']);
        $this->assertSame('kaboom', $notification->getPayload()['exceptionMessage']);
        $this->assertNotNull($notification->getDeduplicationKey());
    }

    public function testNoEmailsConfiguredEmitsNothing(): void
    {
        $this->configureAdminEmails([]);

        $envelope = new Envelope(new stdClass());
        $event = new WorkerMessageFailedEvent($envelope, 'async', new RuntimeException('boom'));

        $this->subscriber->onMessageFailed($event);

        $this->assertCount(0, $this->fetchAllAlertNotifications());
    }

    /**
     * @param array<string> $emails
     */
    private function configureAdminEmails(array $emails): void
    {
        $site = $this->siteRepository->findDefaultSite();
        $site->change(
            $site->getLocales(),
            $site->getContentLocales(),
            'default',
            $site->getSender(),
            $site->getReferenceLocale(),
            $site->getUntranslatedArticleAgeLimitDays(),
            $site->getHomepage(),
            $emails,
        );
        $this->siteRepository->save($site, true);
        $this->siteContext->resetDefaultSite();
    }

    /**
     * @return list<\Xutim\NotificationBundle\Domain\Model\NotificationInterface>
     */
    private function fetchAllAlertNotifications(): array
    {
        return $this->notificationRepository->createQueryBuilder('n')
            ->where('n.recipient IS NULL')
            ->andWhere('n.recipientEmail IS NOT NULL')
            ->getQuery()
            ->getResult();
    }
}
