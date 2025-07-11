<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\BasicTranslatableTrait;
use Xutim\CoreBundle\Entity\TimestampableTrait;
use Xutim\SnippetBundle\Context\SnippetData;
use Xutim\SnippetBundle\Form\SnippetFormData;

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

    #[Column(type: Types::STRING)]
    private string $description;

    #[Column(type: Types::STRING, enumType: SnippetCategory::class)]
    private SnippetCategory $category;

    /** @var Collection<int, SnippetTranslationInterface> */
    #[OneToMany(mappedBy: 'snippet', targetEntity: SnippetTranslationInterface::class, indexBy: 'locale')]
    #[OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    public function __construct(string $code, string $description, SnippetCategory $category)
    {
        $this->id = Uuid::v4();
        $this->change($code, $description, $category);
        $this->translations = new ArrayCollection();
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();
    }

    public function change(string $code, string $description, SnippetCategory $category): void
    {
        $this->code = $code;
        $this->description = $description;
        $this->category = $category;
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

    public function getCategory(): SnippetCategory
    {
        return $this->category;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array<int, SnippetTranslationInterface>
    */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function addTranslation(SnippetTranslationInterface $translation): void
    {
        $this->translations->add($translation);
    }

    public function toFormData(): SnippetFormData
    {
        $array = [];
        /** @var array<string, string> */
        $translations = $this->translations->reduce(
            /** @param array<string, string> $carry */
            function (array $carry, SnippetTranslationInterface $item) {
                $carry[$item->getLocale()] = $item->getContent();

                return $carry;
            },
            $array
        );

        return new SnippetFormData(
            $this->code,
            $this->description,
            $this->category,
            $translations
        );
    }

    public function toData(): SnippetData
    {
        $array = [];
        /** @var array<string, string> */
        $translations = $this->translations->reduce(
            /** @param array<string, string> $carry */
            function (array $carry, SnippetTranslationInterface $item) {
                $carry[$item->getLocale()] = $item->getContent();

                return $carry;
            },
            $array
        );

        return new SnippetData(
            $this->code,
            $this->description,
            $this->category,
            $translations
        );
    }
}
