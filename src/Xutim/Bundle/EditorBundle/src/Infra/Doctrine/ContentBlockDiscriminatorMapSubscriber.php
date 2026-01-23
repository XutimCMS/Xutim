<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Infra\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Xutim\EditorBundle\Entity\ContentBlock;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
class ContentBlockDiscriminatorMapSubscriber
{
    /**
     * @param array<string, class-string<ContentBlock>> $blockTypes
     */
    public function __construct(
        private readonly array $blockTypes,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();

        if ($metadata->name !== ContentBlock::class) {
            return;
        }

        /** @var ClassMetadata<ContentBlock> $metadata */
        $metadata->setDiscriminatorMap($this->blockTypes);
    }
}
