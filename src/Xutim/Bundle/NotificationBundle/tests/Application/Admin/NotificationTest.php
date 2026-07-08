<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Tests\Application\Admin;

use App\Entity\Core\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Data\ArticleData;
use Xutim\CoreBundle\Domain\Factory\ArticleFactory;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Service\TranslatorNotificationService;
use Xutim\CoreBundle\Tests\Application\Admin\AdminApplicationTestCase;
use Xutim\NotificationBundle\Entity\NotificationSeverity;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\DataFixtures\LoadUserFixture;
use Xutim\SecurityBundle\Domain\Factory\UserFactoryInterface;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;

final class NotificationTest extends AdminApplicationTestCase
{
    public function testNotifyingTranslatorsCreatesNotificationForMatchingTranslator(): void
    {
        $translator = $this->createTranslator('de');

        $article = $this->createArticle();

        /** @var TranslatorNotificationService $notificationService */
        $notificationService = static::getContainer()->get(TranslatorNotificationService::class);
        $notificationService->notifyNewTranslationLocales($article, ['de'], LoadUserFixture::USER_EMAIL);

        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = static::getContainer()->get(NotificationRepository::class);
        $notifications = $notificationRepository->findLatestForRecipient($translator);

        $this->assertCount(1, $notifications);
        $this->assertSame('translation_locale_added', $notifications[0]->getType());
        $this->assertStringContainsString('/admin/de/article/edit/' . $article->getId()->toRfc4122(), (string) $notifications[0]->getActionUrl());
    }

    public function testTranslatorCanMarkNotificationReadFromInbox(): void
    {
        $client = static::createClient();
        $translator = $this->createTranslator('de');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = static::getContainer()->get(NotificationRepository::class);
        $notification = new Notification(
            $translator,
            'translation_locale_added',
            NotificationSeverity::Warning,
            'Translation needed',
            'Please translate this article.',
            '/admin/de/article',
            'Open translation',
        );
        $notificationRepository->save($notification, true);

        $client->loginUser($translator);

        $client->request('GET', '/admin/de/notifications');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.list-group-item', 'Translation needed');

        $client->request('POST', '/admin/de/notifications/' . $notification->getId()->toRfc4122() . '/read', server: [
            'HTTP_REFERER' => '/admin/de/notifications',
        ]);
        $this->assertResponseRedirects('/admin/de/notifications');

        $em->clear();
        $reloaded = $notificationRepository->find($notification->getId());
        $this->assertNotNull($reloaded);
        $this->assertTrue($reloaded->isRead());

        $client->request('POST', '/admin/de/notifications/' . $notification->getId()->toRfc4122() . '/unread', server: [
            'HTTP_REFERER' => '/admin/de/notifications',
        ]);
        $this->assertResponseRedirects('/admin/de/notifications');

        $em->clear();
        $reloaded = $notificationRepository->find($notification->getId());
        $this->assertNotNull($reloaded);
        $this->assertFalse($reloaded->isRead());
    }

    public function testEditorCanNotifyTranslatorsWithoutMessage(): void
    {
        $client = static::createClient();
        $translator = $this->createTranslator('fr');
        $article = $this->createArticle();

        $client->loginUser($this->getTestUser());

        $crawler = $client->request('GET', '/admin/en/article/' . $article->getId()->toRfc4122() . '/notify-translators');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Notify translators')->form();
        $form['notification_alert[severity]'] = '0';
        $form['notification_alert[message]'] = '';
        $client->submit($form);
        $this->assertResponseRedirects();

        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = static::getContainer()->get(NotificationRepository::class);
        $notifications = $notificationRepository->findLatestForRecipient($translator);

        $this->assertCount(1, $notifications);
        $this->assertStringContainsString('A new article translation is needed', $notifications[0]->getBody());
    }

    private function createTranslator(string $locale)
    {
        /** @var UserFactoryInterface $userFactory */
        $userFactory = static::getContainer()->get(UserFactoryInterface::class);
        /** @var UserRepositoryInterface $userRepository */
        $userRepository = static::getContainer()->get(UserRepositoryInterface::class);

        $translator = $userFactory->create(
            Uuid::v4(),
            sprintf('translator-%s@example.test', uniqid()),
            'translator',
            LoadUserFixture::USER_PASSWD,
            [UserRoles::ROLE_TRANSLATOR],
            [$locale],
            LoadUserFixture::USER_AVATAR,
        );
        $userRepository->save($translator, true);

        return $translator;
    }

    private function createArticle()
    {
        /** @var ArticleFactory $articleFactory */
        $articleFactory = static::getContainer()->get(ArticleFactory::class);
        /** @var ArticleRepository $articleRepository */
        $articleRepository = static::getContainer()->get(ArticleRepository::class);
        /** @var ContentTranslationRepository $translationRepository */
        $translationRepository = static::getContainer()->get(ContentTranslationRepository::class);

        $suffix = uniqid();
        $article = $articleFactory->create(new ArticleData(
            'standard',
            'Pre',
            'Notification Article ' . $suffix,
            'Sub',
            'notification-article-' . $suffix,
            [],
            'Description',
            'en',
            LoadUserFixture::USER_EMAIL,
            null,
            true,
            [],
        ));

        $articleRepository->save($article);
        $translation = $article->getTranslations()->first();
        assert($translation !== false);
        $translationRepository->save($translation, true);

        return $article;
    }
}
