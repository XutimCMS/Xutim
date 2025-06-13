<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Domain\Model\SnippetTranslationInterface;
use Xutim\CoreBundle\Form\Admin\Dto\SnippetDto;

#[MappedSuperclass]
class Snippet implements SnippetInterface
{
    use TimestampableTrait;
    /** @use BasicTranslatableTrait<SnippetTranslationInterface> */
    use BasicTranslatableTrait;

    #[Id]
    #[Column(type: 'uuid')]
    private Uuid $id;

    #[Column(type: Types::STRING)]
    private string $code;

    /** @var Collection<int, SnippetTranslationInterface> */
    #[OneToMany(mappedBy: 'snippet', targetEntity: SnippetTranslationInterface::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    public function __construct(string $code)
    {
        $this->id = Uuid::v4();
        $this->code = $code;
        $this->translations = new ArrayCollection();
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
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

    public function isRouteType(): bool
    {
        return str_starts_with('route-', $this->code);
    }

    public function change(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return Collection<int, SnippetTranslationInterface>
    */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(SnippetTranslationInterface $translation): void
    {
        $this->translations->add($translation);
    }

    public function toDto(): SnippetDto
    {
        $array = [];
        /** @var array<string, string> */
        $translations = $this->getTranslations()->reduce(
            /** @param array<string, string> $carry */
            function (array $carry, SnippetTranslationInterface $item) {
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
