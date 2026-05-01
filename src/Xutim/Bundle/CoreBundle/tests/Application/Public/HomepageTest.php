<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Application\Public;

use App\Factory\ContentTranslationFactory;
use App\Factory\PageFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Entity\PublicationStatus;
use Xutim\CoreBundle\Repository\SiteRepository;
use Zenstruck\Foundry\Test\Factories;

class HomepageTest extends WebTestCase
{
    use Factories;

    public function testItDisplayHomepage(): void
    {
        $client = static::createClient();
        $this->setHomepage($client, null);

        $client->request('GET', '/');
        $this->assertResponseRedirects();

        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Welcome to Xutim CMS');
    }

    public function testRendersConfiguredHomepagePage(): void
    {
        $client = static::createClient();
        $page = $this->makePageWithPublishedTranslation('en', 'My Custom Home');
        $this->setHomepage($client, $page);

        $client->request('GET', '/en/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'My Custom Home');
        $this->assertSelectorExists(sprintf('main[data-test-page="%s"]', $page->getId()->toRfc4122()));
    }

    public function testFallsBackWhenConfiguredHomepageHasOnlyDraftAndVisitorIsAnonymous(): void
    {
        $client = static::createClient();
        $page = $this->makePageWithDraftTranslation('en', 'Draft Home');
        $this->setHomepage($client, $page);

        $client->request('GET', '/en/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Welcome to Xutim CMS');
    }

    private function makePageWithPublishedTranslation(string $locale, string $title): PageInterface
    {
        $page = PageFactory::createOne();
        $translation = ContentTranslationFactory::createOne([
            'page' => $page,
            'locale' => $locale,
            'title' => $title,
        ]);
        $translation->changeStatus(PublicationStatus::Published);

        return $page;
    }

    private function makePageWithDraftTranslation(string $locale, string $title): PageInterface
    {
        $page = PageFactory::createOne();
        ContentTranslationFactory::createOne([
            'page' => $page,
            'locale' => $locale,
            'title' => $title,
        ]);

        return $page;
    }

    private function setHomepage(KernelBrowser $client, ?PageInterface $page): void
    {
        $container = $client->getContainer();
        $siteRepository = $container->get(SiteRepository::class);
        $siteContext = $container->get(SiteContext::class);

        $site = $siteRepository->findDefaultSite();
        $site->changeHomepage($page);
        $siteRepository->save($site, true);
        $siteContext->resetDefaultSite();
    }
}
