<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\FileTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;

#[MappedSuperclass]
class File implements FileInterface
{
    public const array ALLOWED_IMAGE_EXTENSIONS = [
        'jpg', 'png', 'gif', 'jpeg'
    ];

    public const array ALLOWED_FILE_EXTENSIONS = [
        ...self::ALLOWED_IMAGE_EXTENSIONS,
        'pdf', 'zip', 'doc', 'xls', 'xlsx', 'ods', 'doc',
        'docx', 'odt', 'mp3', 'mp4'
    ];

    use TimestampableTrait;

    /** @use BasicTranslatableTrait<FileTranslationInterface> */
    use BasicTranslatableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true, nullable: false)]
    private Uuid $id;

    #[Column(type: 'string', length: 6, unique: true, nullable: false)]
    private string $reference;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $dataPath;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $extension;

    #[Column(type: 'string', length: 255, nullable: false)]
    private string $copyright;

    #[Column(type: 'string', length: 64, nullable: false, unique: true)]
    private string $hash;

    /** @var Collection<int, ArticleInterface>  */
    #[OneToMany(targetEntity: ArticleInterface::class, mappedBy: 'featuredImage')]
    #[JoinColumn(nullable: true)]
    private Collection $featuredArticles;

    /** @var Collection<int, PageInterface>  */
    #[OneToMany(targetEntity: PageInterface::class, mappedBy: 'featuredImage')]
    #[JoinColumn(nullable: true)]
    private Collection $featuredPages;

    /** @var Collection<int, TagInterface>  */
    #[OneToMany(targetEntity: TagInterface::class, mappedBy: 'featuredImage')]
    #[JoinColumn(nullable: true)]
    private Collection $featuredTags;

    /** @var Collection<int, ArticleInterface>  */
    #[ManyToMany(targetEntity: ArticleInterface::class, inversedBy: 'files')]
    #[JoinColumn(name: 'file_id', nullable: true)]
    #[InverseJoinColumn(name: 'article_id')]
    #[JoinTable(name: 'xutim_file_article')]
    private Collection $articles;

    /** @var Collection<int, PageInterface>  */
    #[ManyToMany(targetEntity: PageInterface::class, inversedBy: 'files')]
    #[JoinColumn(name: 'file_id', nullable: true)]
    #[InverseJoinColumn(name: 'page_id')]
    #[JoinTable(name: 'xutim_file_page')]
    private Collection $pages;

    /** @var Collection<int, BlockItemInterface> */
    #[OneToMany(mappedBy: 'file', targetEntity: BlockItemInterface::class)]
    private Collection $blockItems;

    /** @var Collection<int, FileTranslationInterface> */
    #[OneToMany(mappedBy: 'file', targetEntity: FileTranslationInterface::class)]
    private Collection $translations;

    public function __construct(
        Uuid $id,
        string $dataPath,
        string $extension,
        string $reference,
        string $hash,
        string $copyright,
        ?ArticleInterface $article = null,
        ?PageInterface $page = null
    ) {
        $this->id = $id;
        $this->dataPath = $dataPath;
        $this->extension = $extension;
        $this->reference = $reference;
        $this->hash = $hash;
        $this->copyright = $copyright;
        $this->articles = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->featuredPages = new ArrayCollection();
        $this->featuredArticles = new ArrayCollection();
        $this->featuredTags = new ArrayCollection();
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

    public function addTranslation(FileTranslationInterface $trans): void
    {
        if ($this->translations->contains($trans) === true) {
            return;
        }

        $this->translations->add($trans);
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
        return in_array(strtolower($this->extension), self::ALLOWED_IMAGE_EXTENSIONS, true);
    }

    /**
     * @return Collection<int, FileTranslationInterface>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addPage(PageInterface $page): void
    {
        $this->pages->add($page);
    }

    public function addArticle(ArticleInterface $article): void
    {
        $this->articles->add($article);
    }

    public function addObject(PageInterface|ArticleInterface $object): void
    {
        if ($object instanceof PageInterface) {
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
     * @return Collection<int, PageInterface>
    */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    /**
     * @return Collection<int, ArticleInterface>
    */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function removePage(PageInterface $page): void
    {
        $this->pages->removeElement($page);
    }

    public function removeArticle(ArticleInterface $article): void
    {
        $this->articles->removeElement($article);
    }
    
    public function getTranslationByLocaleOrDefault(string $locale): FileTranslationInterface
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

    public function changeCopyright(string $copyright): void
    {
        $this->copyright = $copyright;
    }

    public function getCopyright(): string
    {
        return $this->copyright;
    }
}
