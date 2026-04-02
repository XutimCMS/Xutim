<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Event\EventTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<EventTranslation>
 */
final class EventTranslationFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return EventTranslation::class;
    }

    protected function defaults(): array
    {
        return [
            'title' => self::faker()->sentence(3),
            'location' => self::faker()->city(),
            'description' => self::faker()->paragraph(),
            'locale' => 'en',
            'event' => EventFactory::new(),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}
