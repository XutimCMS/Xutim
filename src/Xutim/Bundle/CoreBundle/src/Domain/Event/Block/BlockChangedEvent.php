<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Event\Block;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\Domain\DomainEvent;

final readonly class BlockChangedEvent implements DomainEvent
{
    public function __construct(
        public Uuid $id,
        public string $name,
        public string $description,
        public DateTimeImmutable $createdAt,
        public ?string $layout,
    ) {
    }

    public static function fromBlock(BlockInterface $block): self
    {
        return new self(
            $block->getId(),
            $block->getName(),
            $block->getDescription(),
            $block->getCreatedAt(),
            $block->getLayout()
        );
    }
}
