<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Repository\FileRepository;

#[Entity(repositoryClass: FileRepository::class)]
class File
{
    public const array ALLOWED_IMAGE_EXTENSIONS = [
        'jpg', 'png', 'svg', 'gif'
    ];
    use TimestampableTrait;

    /** @use TranslatableTrait<FileTranslation> */
    use TranslatableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true, nullable: false)]
    private Uuid $id;

    #[Column(type: 'string', length: 6, unique: true, nullable: false)]
    private string $reference;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $dataPath;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $extension;

    /** @var Collection<int, Article>  */
    #[ManyToMany(targetEntity: Article::class, inversedBy: 'files')]
    #[JoinColumn(nullable: true)]
    private Collection $articles;

    /** @var Collection<int, Page>  */
    #[ManyToMany(targetEntity: Page::class, inversedBy: 'files')]
    #[JoinColumn(nullable: true)]
    private Collection $pages;

    /** @var Collection<int, BlockItem> */
    #[OneToMany(mappedBy: 'file', targetEntity: BlockItem::class)]
    private Collection $blockItems;

    /** @var Collection<int, FileTranslation> */
    #[OneToMany(mappedBy: 'file', targetEntity: FileTranslation::class)]
    private Collection $translations;

    public function __construct(
        Uuid $id,
        string $name,
        string $alt,
        string $locale,
        string $dataPath,
        string $extension,
        string $reference,
        ?Article $article = null,
        ?Page $page = null
    ) {
        $this->translations = new ArrayCollection([new FileTranslation($locale, $name, $alt, $this)]);

        $this->id = $id;
        $this->dataPath = $dataPath;
        $this->extension = $extension;
        $this->reference = $reference;
        $this->articles = new ArrayCollection();
        $this->pages = new ArrayCollection();
        if ($article !== null) {
            $this->articles->add($article);
        }
        if ($page !== null) {
            $this->pages->add($page);
        }
        $this->blockItems = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeConnections(): void
    {
        foreach ($this->pages as $page) {
            $page->removeFile($this);
        }
        $this->pages->clear();

        foreach ($this->articles as $article) {
            $article->removeFile($this);
        }
        $this->articles->clear();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->dataPath;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function isImage(): bool
    {
        return in_array($this->extension, self::ALLOWED_IMAGE_EXTENSIONS, true);
    }

    /**
     * @return Collection<int, FileTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addPage(Page $page): void
    {
        $this->pages->add($page);
    }

    public function addArticle(Article $article): void
    {
        $this->articles->add($article);
    }

    public function addObject(Page|Article $object): void
    {
        if ($object instanceof Page) {
            $this->addPage($object);

            return;
        }

        $this->addArticle($object);
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    /**
    * @return Collection<int, Page>
    */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    /**
    * @return Collection<int, Article>
    */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function removePage(Page $page): void
    {
        $this->pages->removeElement($page);
    }

    public function removeArticle(Article $article): void
    {
        $this->articles->removeElement($article);
    }
    
    public function getTranslationByLocaleOrDefault(string $locale): FileTranslation
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('locale', $locale))
            ->setFirstResult(0)
            ->setMaxResults(1);
        /** @var FileTranslation|false $translation */
        $translation = $this->translations->matching($criteria)->first();

        if ($translation === false) {
            $translation = $this->translations->first();
            Assert::notFalse($translation);

            return $translation;
        }

        return $translation;
    }
}
