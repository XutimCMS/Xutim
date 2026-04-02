<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Event\Event;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Event>
 */
final class EventFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Event::class;
    }

    protected function defaults(): array
    {
        return [
            'startsAt' => new \DateTimeImmutable('+1 day'),
            'endsAt' => new \DateTimeImmutable('+2 days'),
            'article' => null,
            'page' => null,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
