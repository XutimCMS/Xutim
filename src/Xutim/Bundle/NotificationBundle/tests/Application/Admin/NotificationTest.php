<?php

declare(strict_types=1);

namespace Xutim\NotificationBundle\Tests\Application\Admin;

use App\Entity\Core\Notification;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Data\ArticleData;
use Xutim\CoreBundle\Domain\Factory\ArticleFactory;
use Xutim\CoreBundle\Message\Command\Article\EditArticleTranslationLocalesCommand;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Tests\Application\Admin\AdminApplicationTestCase;
use Xutim\NotificationBundle\Entity\NotificationSeverity;
use Xutim\NotificationBundle\Repository\NotificationRepository;
use Xutim\SecurityBundle\DataFixtures\LoadUserFixture;
use Xutim\SecurityBundle\Domain\Factory\UserFactoryInterface;
use Xutim\SecurityBundle\Repository\UserRepositoryInterface;
use Xutim\SecurityBundle\Security\UserRoles;

final class NotificationTest extends AdminApplicationTestCase
{
    public function testAddingTranslationLocaleCreatesNotificationForMatchingTranslator(): void
    {
        $translator = $this->createTranslator('de');

        $article = $this->createArticle();

        /** @var MessageBusInterface $bus */
        $bus = static::getContainer()->get(MessageBusInterface::class);
        $bus->dispatch(new EditArticleTranslationLocalesCommand(
            $article->getId(),
            false,
            ['de'],
            LoadUserFixture::USER_EMAIL,
        ));

        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = static::getContainer()->get(NotificationRepository::class);
        $notifications = $notificationRepository->findLatestForRecipient($translator);

        $this->assertCount(1, $notifications);
        $this->assertSame('translation_locale_added', $notifications[0]->getType());
        $this->assertStringContainsString('/admin/de/article/edit/' . $article->getId()->toRfc4122(), (string) $notifications[0]->getActionUrl());
    }

    public function testTranslatorCanMarkNotificationReadFromInbox(): void
    {
        $translator = $this->createTranslator('de');

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

        $client = static::createClient();
        $client->loginUser($translator);

        $client->request('GET', '/admin/de/notifications');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('strong', 'Translation needed');

        $client->request('POST', '/admin/de/notifications/' . $notification->getId()->toRfc4122() . '/read');
        $this->assertResponseRedirects('/admin/de/article');

        $reloaded = $notificationRepository->find($notification->getId());
        $this->assertNotNull($reloaded);
        $this->assertTrue($reloaded->isRead());
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
            false,
            [],
        ));

        $articleRepository->save($article);
        $translationRepository->save($article->getDefaultTranslation(), true);

        return $article;
    }
}
