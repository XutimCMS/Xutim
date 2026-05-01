<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Core\Page;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Page>
 */
final class PageFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Page::class;
    }

    protected function defaults(): array
    {
        return [
            'layout' => 'standard',
            'locales' => [],
            'parent' => null,
            'featuredImage' => null,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
