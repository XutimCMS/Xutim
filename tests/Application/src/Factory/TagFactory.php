<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Core\Tag;
use Xutim\CoreBundle\Entity\Color;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Tag>
 */
final class TagFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Tag::class;
    }

    protected function defaults(): array
    {
        return [
            'color' => new Color(Color::DEFAULT_VALUE_HEX),
            'featuredImage' => null,
            'layout' => null,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
