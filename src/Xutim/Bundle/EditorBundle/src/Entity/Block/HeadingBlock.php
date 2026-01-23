<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Entity\Block;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Xutim\EditorBundle\Domain\Model\ContentBlockInterface;
use Xutim\EditorBundle\Domain\Model\ContentDraftInterface;
use Xutim\EditorBundle\Entity\ContentBlock;

#[Entity]
class HeadingBlock extends ContentBlock
{
    public const string TYPE = 'heading';

    #[Column(type: Types::TEXT)]
    private string $html = '';

    #[Column(type: Types::INTEGER)]
    private int $level = 2;

    public function __construct(
        ContentDraftInterface $draft,
        string $html = '',
        int $level = 2,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ) {
        parent::__construct($draft, $parent, $slot, $position);
        $this->html = $html;
        $this->level = $level;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function setHtml(string $html): void
    {
        $this->html = $html;
        $this->updates();
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
        $this->updates();
    }
}
