<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[UniqueConstraint(columns: ['locale', 'slug'])]
trait ContentTranslationTrait
{
    #[Column(type: 'string', length: 255, nullable: false)]
    private string $preTitle;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $title;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $subTitle;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $slug;

    /**
     * @var array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * }
     */
    #[Column(type: Types::JSON, nullable: false)]
    private array $content;

    #[Column(type: 'string', length: 10, nullable: false)]
    private string $locale;

    #[Column(type: 'text', nullable: false)]
    private string $description;

    #[Column(type: 'boolean', nullable: false, options: ['comment' => 'True when referenced translation has changed while a translation was already published.'])]
    private bool $hasUntranslatedChange;

    /**
     * @param array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $content
     */
    public function change(
        string $preTitle,
        string $title,
        string $subTitle,
        string $slug,
        array $content,
        string $locale,
        string $description
    ): void {
        $this->updatedAt = new DateTimeImmutable();
        $this->preTitle = $preTitle;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->slug = $slug;
        $this->content = $content;
        $this->locale = $locale;
        $this->description = $description;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getPreTitle(): string
    {
        return $this->preTitle;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubTitle(): string
    {
        return $this->subTitle;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array{}|array{
     *     time: int,
     *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * }
    */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @phpstan-assert-if-true array{
     *     time: int,
     *     blocks: array<array{id: string, type: string, data: array<string, mixed>}>,
     *     version: string
     * } $this->content
     */
    public function hasContent(): bool
    {
        if (count($this->content) === 0) {
            return false;
        }

        if (array_key_exists('blocks', $this->content) === false) {
            return false;
        }

        if (count($this->content['blocks']) === 0) {
            return false;
        }

        return true;
    }

    public function hasUntranslatedChange(): bool
    {
        return $this->hasUntranslatedChange;
    }

    public function newTranslationChange(): void
    {
        $this->hasUntranslatedChange = true;
    }

    /**
     * @return null|array{id: string, type: string, data: array<string, mixed>}
     */
    public function getMainImageBlock(): ?array
    {
        if ($this->hasContent() === false) {
            return null;
        }

        $blocks = $this->content['blocks'];
        if ($blocks[array_key_first($blocks)]['type'] === 'image') {
            return $blocks[array_key_first($blocks)];
        }

        return null;
    }
}
