<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Repository\SnippetTranslationRepository;

#[Entity(repositoryClass: SnippetTranslationRepository::class)]
class SnippetTranslation
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::STRING)]
    private string $locale;

    #[Column(type: Types::TEXT)]
    private string $content;

    #[ManyToOne(targetEntity: Snippet::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: false)]
    private Snippet $snippet;

    public function __construct(Snippet $snippet, string $locale, string $content)
    {
        $this->id = Uuid::v4();
        $this->snippet = $snippet;
        $this->locale = $locale;
        $this->content = $content;
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSnippet(): Snippet
    {
        return $this->snippet;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function update(string $content): void
    {
        $this->content = $content;
    }
}
