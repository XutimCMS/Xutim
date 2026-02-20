<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Entity\Block;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;
use Xutim\EditorBundle\Entity\ContentBlock;
use Xutim\MediaBundle\Domain\Model\MediaInterface;

#[Entity]
class ImageBlock extends ContentBlock
{
    public const string TYPE = 'image';

    #[ManyToOne(targetEntity: MediaInterface::class)]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?MediaInterface $file = null;

    #[Column(type: Types::TEXT, nullable: true)]
    private ?string $caption = null;

    public function __construct(
        ContentDraftInterface $draft,
        ?MediaInterface $file = null,
        ?string $caption = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ) {
        parent::__construct($draft, $parent, $slot, $position);
        $this->file = $file;
        $this->caption = $caption;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getFile(): ?MediaInterface
    {
        return $this->file;
    }

    public function setFile(?MediaInterface $file): void
    {
        $this->file = $file;
        $this->updates();
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): void
    {
        $this->caption = $caption;
        $this->updates();
    }

    public function hasFile(): bool
    {
        return $this->file !== null;
    }
}
