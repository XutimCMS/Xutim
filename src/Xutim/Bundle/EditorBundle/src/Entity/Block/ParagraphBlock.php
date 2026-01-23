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
class ParagraphBlock extends ContentBlock
{
    public const string TYPE = 'paragraph';

    #[Column(type: Types::TEXT)]
    private string $html = '';

    public function __construct(
        ContentDraftInterface $draft,
        string $html = '',
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ) {
        parent::__construct($draft, $parent, $slot, $position);
        $this->html = $html;
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
}
