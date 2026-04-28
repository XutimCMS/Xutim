<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Exception\CannotDeleteHomepageException;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\MenuItemRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Service\ContentTranslationService;
use Xutim\SecurityBundle\Service\UserStorage;

final class ContentTranslationServiceTest extends TestCase
{
    public function testDeletePageThrowsWhenPageIsConfiguredHomepage(): void
    {
        $homepageId = Uuid::v4();
        $page = $this->createStub(PageInterface::class);
        $page->method('getId')->willReturn($homepageId);

        $menuItemRepo = $this->createMock(MenuItemRepository::class);
        $menuItemRepo->expects($this->never())->method('findOneBy');

        $service = $this->buildService(
            menuItemRepo: $menuItemRepo,
            homepageId: $homepageId->toRfc4122(),
        );

        $this->expectException(CannotDeleteHomepageException::class);
        $service->deletePage($page);
    }

    public function testDeletePageProceedsWhenPageIsNotHomepage(): void
    {
        $page = $this->createStub(PageInterface::class);
        $page->method('getId')->willReturn(Uuid::v4());
        $page->method('canBeDeleted')->willReturn(false);

        $service = $this->buildService(
            homepageId: Uuid::v4()->toRfc4122(),
        );

        $this->assertFalse($service->deletePage($page));
    }

    private function buildService(
        ?MenuItemRepository $menuItemRepo = null,
        ?string $homepageId = null,
    ): ContentTranslationService {
        $siteContext = $this->createStub(SiteContext::class);
        $siteContext->method('getHomepageId')->willReturn($homepageId);

        return new ContentTranslationService(
            $this->createStub(LogEventFactory::class),
            $this->createStub(UserStorage::class),
            $this->createStub(ContentTranslationRepository::class),
            $this->createStub(ArticleRepository::class),
            $this->createStub(PageRepository::class),
            $this->createStub(LogEventRepository::class),
            $menuItemRepo ?? $this->createStub(MenuItemRepository::class),
            $siteContext,
        );
    }
}
