<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Core\ContentTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ContentTranslation>
 */
final class ContentTranslationFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ContentTranslation::class;
    }

    protected function defaults(): array
    {
        return [
            'preTitle' => '',
            'title' => self::faker()->sentence(3),
            'subTitle' => '',
            'slug' => self::faker()->unique()->slug(),
            'content' => ['blocks' => []],
            'locale' => 'en',
            'description' => '',
            'page' => null,
            'article' => null,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
