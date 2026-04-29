<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Sitemap;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Routing\ContentTranslationRouteGenerator;
use Xutim\CoreBundle\Sitemap\SitemapGenerator;
use Xutim\SnippetBundle\Repository\SnippetRepository;
use Xutim\SnippetBundle\Routing\SnippetUrlGenerator;

final class SitemapGeneratorTest extends TestCase
{
    private string $sitemapFile;

    protected function setUp(): void
    {
        $this->sitemapFile = (string) tempnam(sys_get_temp_dir(), 'sitemap_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->sitemapFile)) {
            unlink($this->sitemapFile);
        }
    }

    public function testHomepagePageIsExcludedFromSitemapPages(): void
    {
        $homepageId = Uuid::v4();
        $homepagePage = $this->buildPage($homepageId, '/en/home');
        $regularPage = $this->buildPage(Uuid::v4(), '/en/about');

        $items = $this->captureSitemapItems(
            pages: [$homepagePage, $regularPage],
            homepageId: $homepageId->toRfc4122(),
        );

        $locs = array_column($items, 'loc');
        $this->assertNotContains('https://example.test/en/home', $locs);
        $this->assertContains('https://example.test/en/about', $locs);
        $this->assertContains('https://example.test/', $locs);
    }

    public function testAllPagesAreIncludedWhenNoHomepageConfigured(): void
    {
        $pageA = $this->buildPage(Uuid::v4(), '/en/a');
        $pageB = $this->buildPage(Uuid::v4(), '/en/b');

        $items = $this->captureSitemapItems(
            pages: [$pageA, $pageB],
            homepageId: null,
        );

        $locs = array_column($items, 'loc');
        $this->assertContains('https://example.test/en/a', $locs);
        $this->assertContains('https://example.test/en/b', $locs);
    }

    /**
     * @param list<PageInterface> $pages
     * @return list<array{loc: string, ...}>
     */
    private function captureSitemapItems(array $pages, ?string $homepageId): array
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturnCallback(
            fn (string $name, array $params = []) => $name === 'homepage' ? '/' : '/'.$name
        );

        $pageRepo = $this->createStub(PageRepository::class);
        $pageRepo->method('findAll')->willReturn($pages);

        $articleRepo = $this->createStub(ArticleRepository::class);
        $articleRepo->method('findAll')->willReturn([]);

        $tagRepo = $this->createStub(TagRepository::class);
        $tagRepo->method('findAllPublished')->willReturn([]);

        $snippetRepo = $this->createStub(SnippetRepository::class);
        $snippetRepo->method('findByCode')->willReturn(null);

        $siteContext = $this->createStub(SiteContext::class);
        $siteContext->method('getMainLocales')->willReturn(['en']);
        $siteContext->method('getHomepageId')->willReturn($homepageId);

        $transRouteGenerator = $this->createStub(ContentTranslationRouteGenerator::class);
        $transRouteGenerator->method('generatePath')->willReturnCallback(
            fn (ContentTranslationInterface $trans) => '/en/'.$trans->getSlug()
        );

        $captured = [];
        $twig = $this->createStub(Environment::class);
        $twig->method('render')->willReturnCallback(
            function (string $template, array $context = []) use (&$captured): string {
                $captured = $context['items'] ?? [];
                return '';
            }
        );

        $snippetUrlGenerator = new SnippetUrlGenerator($snippetRepo);

        $generator = new SitemapGenerator(
            $router,
            $pageRepo,
            $articleRepo,
            $tagRepo,
            $snippetRepo,
            $siteContext,
            $transRouteGenerator,
            $twig,
            $snippetUrlGenerator,
            $this->sitemapFile,
            'example.test',
        );

        $generator->generate();

        return $captured;
    }

    private function buildPage(Uuid $id, string $publicPath): PageInterface
    {
        $slug = ltrim(substr($publicPath, strrpos($publicPath, '/') ?: 0), '/');

        $translation = $this->createStub(ContentTranslationInterface::class);
        $translation->method('getSlug')->willReturn($slug);
        $translation->method('getLocale')->willReturn('en');
        $translation->method('getUpdatedAt')->willReturn(new \DateTimeImmutable());

        $page = $this->createStub(PageInterface::class);
        $page->method('getId')->willReturn($id);
        $page->method('getPublishedTranslations')->willReturn(new ArrayCollection([$translation]));

        return $page;
    }
}
