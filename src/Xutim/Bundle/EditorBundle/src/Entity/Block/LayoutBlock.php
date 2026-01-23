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
class LayoutBlock extends ContentBlock
{
    public const string TYPE = 'layout';

    #[Column(type: Types::STRING, length: 100)]
    private string $template;

    /** @var array<string, mixed>|null */
    #[Column(type: Types::JSON, nullable: true)]
    private ?array $settings = null;

    /**
     * @param array<string, mixed>|null $settings
     */
    public function __construct(
        ContentDraftInterface $draft,
        string $template,
        ?array $settings = null,
        ?ContentBlockInterface $parent = null,
        ?int $slot = null,
        int $position = 0,
    ) {
        parent::__construct($draft, $parent, $slot, $position);
        $this->template = $template;
        $this->settings = $settings;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
        $this->updates();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }

    /**
     * @param array<string, mixed>|null $settings
     */
    public function setSettings(?array $settings): void
    {
        $this->settings = $settings;
        $this->updates();
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }
}
