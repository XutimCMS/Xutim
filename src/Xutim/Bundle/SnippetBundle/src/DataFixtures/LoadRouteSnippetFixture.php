<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Xutim\SnippetBundle\Domain\Factory\SnippetFactoryInterface;
use Xutim\SnippetBundle\Domain\Factory\SnippetTranslationFactoryInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;

class LoadRouteSnippetFixture extends Fixture
{
    public function __construct(
        private readonly SnippetFactoryInterface $snippetFactory,
        private readonly SnippetTranslationFactoryInterface $translationFactory
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $newsSnippet = $this->snippetFactory->create(
            'route-news',
            'URL path for news page',
            SnippetCategory::Route
        );
        $newsEn = $this->translationFactory->create($newsSnippet, 'en', 'news');
        $newsFr = $this->translationFactory->create($newsSnippet, 'fr', 'actualites');
        $newsSnippet->addTranslation($newsEn);
        $newsSnippet->addTranslation($newsFr);

        $searchSnippet = $this->snippetFactory->create(
            'route-search',
            'URL path for search page',
            SnippetCategory::Route
        );
        $searchEn = $this->translationFactory->create($searchSnippet, 'en', 'search');
        $searchFr = $this->translationFactory->create($searchSnippet, 'fr', 'recherche');
        $searchSnippet->addTranslation($searchEn);
        $searchSnippet->addTranslation($searchFr);

        $manager->persist($newsSnippet);
        $manager->persist($newsEn);
        $manager->persist($newsFr);
        $manager->persist($searchSnippet);
        $manager->persist($searchEn);
        $manager->persist($searchFr);

        $manager->flush();
    }
}
