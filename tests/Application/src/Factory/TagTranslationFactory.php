<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Core\TagTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<TagTranslation>
 */
final class TagTranslationFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return TagTranslation::class;
    }

    protected function defaults(): array
    {
        return [
            'name' => self::faker()->word(),
            'slug' => self::faker()->unique()->slug(),
            'locale' => 'en',
            'tag' => TagFactory::new(),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
