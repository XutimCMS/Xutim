<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\TimestampableTrait;

#[MappedSuperclass]
class SnippetTranslation implements SnippetTranslationInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::STRING)]
    private string $locale;

    #[Column(type: Types::TEXT)]
    private string $content;

    #[ManyToOne(targetEntity: SnippetInterface::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: false)]
    private SnippetInterface $snippet;

    public function __construct(SnippetInterface $snippet, string $locale, string $content)
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

    public function getSnippet(): SnippetInterface
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
