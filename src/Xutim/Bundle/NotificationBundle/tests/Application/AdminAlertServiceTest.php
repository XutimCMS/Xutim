<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Tests\Application;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\NotificationBundle\Entity\NotificationSeverity;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\NotificationBundle\Service\AdminAlertService;

final class AdminAlertServiceTest extends KernelTestCase
{
    private AdminAlertService $service;
    private NotificationRepository $notificationRepository;
    private SiteRepository $siteRepository;
    private SiteContext $siteContext;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->service = $container->get(AdminAlertService::class);
        $this->notificationRepository = $container->get(NotificationRepository::class);
        $this->siteRepository = $container->get(SiteRepository::class);
        $this->siteContext = $container->get(SiteContext::class);
    }

    public function testEmptyAdminEmailsCreatesNoNotifications(): void
    {
        $this->configureAdminEmails([]);

        $this->service->notify(
            'failed_job',
            NotificationSeverity::Critical,
            'Title',
            'Body',
        );

        $this->assertCount(0, $this->fetchAllAlertNotifications());
    }

    public function testFiresOneNotificationPerAdminEmail(): void
    {
        $this->configureAdminEmails(['ops1@example.com', 'ops2@example.com']);

        $this->service->notify(
            'failed_job',
            NotificationSeverity::Critical,
            'Job failed',
            'Boom.',
        );

        $notifications = $this->fetchAllAlertNotifications();
        $this->assertCount(2, $notifications);
        $emails = array_map(fn ($n) => $n->getRecipientEmail(), $notifications);
        sort($emails);
        $this->assertSame(['ops1@example.com', 'ops2@example.com'], $emails);
        $this->assertNull($notifications[0]->getRecipient());
    }

    public function testDedupKeyPreventsRepeatNotificationsWithinWindow(): void
    {
        $this->configureAdminEmails(['ops@example.com']);

        $key = 'failed_job:App\\Foo:' . intdiv(time(), 3600);

        $this->service->notify('failed_job', NotificationSeverity::Critical, 't', 'b', null, $key);
        $this->service->notify('failed_job', NotificationSeverity::Critical, 't', 'b', null, $key);

        $this->assertCount(1, $this->fetchAllAlertNotifications());
    }

    public function testNoDedupKeyAllowsMultipleCalls(): void
    {
        $this->configureAdminEmails(['ops@example.com']);

        $this->service->notify('failed_job', NotificationSeverity::Critical, 't', 'b');
        $this->service->notify('failed_job', NotificationSeverity::Critical, 't', 'b');

        $this->assertCount(2, $this->fetchAllAlertNotifications());
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
