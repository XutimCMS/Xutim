<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\FileTranslationInterface;

#[MappedSuperclass]
class FileTranslation implements FileTranslationInterface
{
    use TimestampableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true, nullable: false)]
    private Uuid $id;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[Column(type: 'text', nullable: false)]
    private string $alt;

    #[Column(type: 'string', length: 10, nullable: false)]
    private string $locale;

    #[ManyToOne(targetEntity: FileInterface::class, inversedBy: 'translations')]
    #[JoinColumn(nullable: false)]
    private FileInterface $file;

    public function __construct(
        string $locale,
        string $name,
        string $alt,
        FileInterface $file
    ) {
        $this->id = Uuid::v4();
        $this->locale = $locale;
        $this->name = $name;
        $this->alt = $alt;
        $this->file = $file;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function update(string $name, string $alt): void
    {
        $this->name = $name;
        $this->alt = $alt;
        $this->updates();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function getFile(): FileInterface
    {
        return $this->file;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
