<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Event;

use Symfony\Component\Uid\Uuid;
use Xutim\Domain\DomainEvent;

final readonly class VariantsRegeneratedEvent implements DomainEvent
{
    /**
     * @param list<string> $presets      Presets that were regenerated
     * @param int          $variantCount Number of variant files generated
     */
    public function __construct(
        public Uuid $mediaId,
        public array $presets,
        public int $variantCount,
    ) {
    }
}
