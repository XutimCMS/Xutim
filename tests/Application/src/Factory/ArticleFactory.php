<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Core\Article;
use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Article>
 */
final class ArticleFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Article::class;
    }

    protected function defaults(): array
    {
        return [
            'layout' => 'standard',
            'translationLocales' => [],
            'tags' => new ArrayCollection(),
            'featuredImage' => null,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }

    public function withRestrictedLocales(array $locales): static
    {
        return $this->afterInstantiate(function (Article $article) use ($locales): void {
            $article->changeAllTranslationLocales(false);
            $article->changeTranslationLocales($locales);
        });
    }
}
