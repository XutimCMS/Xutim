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
class ListItemBlock extends ContentBlock
{
    public const string TYPE = 'list_item';

    public const string LIST_TYPE_UNORDERED = 'unordered';
    public const string LIST_TYPE_ORDERED = 'ordered';
    public const string LIST_TYPE_CHECKLIST = 'checklist';

    #[Column(type: Types::TEXT)]
    private string $html = '';

    #[Column(type: Types::STRING, length: 20)]
    private string $listType = self::LIST_TYPE_UNORDERED;

    #[Column(type: Types::INTEGER)]
    private int $indent = 0;

    #[Column(type: Types::BOOLEAN)]
    private bool $checked = false;

    public function __construct(
        ContentDraftInterface $draft,
        string $html = '',
        string $listType = self::LIST_TYPE_UNORDERED,
        int $indent = 0,
        bool $checked = false,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ) {
        parent::__construct($draft, $parent, $slot, $position);
        $this->html = $html;
        $this->listType = $listType;
        $this->indent = $indent;
        $this->checked = $checked;
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

    public function getListType(): string
    {
        return $this->listType;
    }

    public function setListType(string $listType): void
    {
        $this->listType = $listType;
        $this->updates();
    }

    public function getIndent(): int
    {
        return $this->indent;
    }

    public function setIndent(int $indent): void
    {
        $this->indent = $indent;
        $this->updates();
    }

    public function isChecked(): bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
        $this->updates();
    }

    public function isChecklist(): bool
    {
        return $this->listType === self::LIST_TYPE_CHECKLIST;
    }

    public function isOrdered(): bool
    {
        return $this->listType === self::LIST_TYPE_ORDERED;
    }

    public function isUnordered(): bool
    {
        return $this->listType === self::LIST_TYPE_UNORDERED;
    }
}
