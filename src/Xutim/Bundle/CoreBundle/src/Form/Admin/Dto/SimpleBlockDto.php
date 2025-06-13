<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

use Xutim\CoreBundle\Domain\Model\Coordinates;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Entity\Color;

final readonly class SimpleBlockDto
{
    public function __construct(
        public ?FileInterface $file,
        public ?SnippetInterface $snippet,
        public ?TagInterface $tag,
        public ?int $position,
        public ?string $link,
        public Color $color,
        public ?string $fileDescription,
        public ?Coordinates $coordinates
    ) {
    }

    public function hasFile(): bool
    {
        return $this->file !== null;
    }

    public function toBlockItemDto(): BlockItemDto
    {
        return new BlockItemDto(null, null, $this->file, $this->snippet, $this->tag, $this->position, $this->link, $this->color->getHex(), $this->fileDescription, $this->coordinates);
    }
}
