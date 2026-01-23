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
class CodeBlock extends ContentBlock
{
    public const string TYPE = 'code';

    #[Column(type: Types::TEXT)]
    private string $code = '';

    #[Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $language = null;

    public function __construct(
        ContentDraftInterface $draft,
        string $code = '',
        ?string $language = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ) {
        parent::__construct($draft, $parent, $slot, $position);
        $this->code = $code;
        $this->language = $language;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
        $this->updates();
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): void
    {
        $this->language = $language;
        $this->updates();
    }
}
