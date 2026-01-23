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
class EmbedBlock extends ContentBlock
{
    public const string TYPE = 'embed';

    public const string SERVICE_YOUTUBE = 'youtube';
    public const string SERVICE_VIMEO = 'vimeo';
    public const string SERVICE_TWITTER = 'twitter';
    public const string SERVICE_INSTAGRAM = 'instagram';
    public const string SERVICE_OTHER = 'other';

    #[Column(type: Types::STRING, length: 50)]
    private string $service = self::SERVICE_OTHER;

    #[Column(type: Types::TEXT)]
    private string $source = '';

    #[Column(type: Types::TEXT, nullable: true)]
    private ?string $caption = null;

    public function __construct(
        ContentDraftInterface $draft,
        string $service = self::SERVICE_OTHER,
        string $source = '',
        ?string $caption = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ) {
        parent::__construct($draft, $parent, $slot, $position);
        $this->service = $service;
        $this->source = $source;
        $this->caption = $caption;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function setService(string $service): void
    {
        $this->service = $service;
        $this->updates();
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
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

    public function isYoutube(): bool
    {
        return $this->service === self::SERVICE_YOUTUBE;
    }

    public function isVimeo(): bool
    {
        return $this->service === self::SERVICE_VIMEO;
    }
}
