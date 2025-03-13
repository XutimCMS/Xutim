<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Form\Admin\Dto\SnippetDto;
use Xutim\CoreBundle\Repository\SnippetRepository;

#[Entity(repositoryClass: SnippetRepository::class)]
class Snippet
{
    use TimestampableTrait;
    /** @use TranslatableTrait<SnippetTranslation> */
    use TranslatableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::STRING)]
    private string $code;

    /** @var Collection<int, SnippetTranslation> */
    #[OneToMany(mappedBy: 'snippet', targetEntity: SnippetTranslation::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    public function __construct(string $code)
    {
        $this->id = Uuid::v4();
        $this->code = $code;
        $this->translations = new ArrayCollection();
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->getCode();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function change(string $code): void
    {
        $this->code = $code;
    }

    /**
    * @return Collection<int, SnippetTranslation>
    */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(SnippetTranslation $translation): void
    {
        $this->translations->add($translation);
    }

    public function toDto(): SnippetDto
    {
        $array = [];
        /** @var array<string, string> */
        $translations = $this->getTranslations()->reduce(
            /** @param array<string, string> $carry */
            function (array $carry, SnippetTranslation $item) {
                $carry[$item->getLocale()] = $item->getContent();

                return $carry;
            },
            $array
        );

        return new SnippetDto(
            $this->getCode(),
            $translations
        );
    }
}
