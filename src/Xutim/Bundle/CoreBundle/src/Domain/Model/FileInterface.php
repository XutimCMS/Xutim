<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

interface FileInterface
{
    public function removeConnections(): void;

    public function getId(): Uuid;

    public function getFileName(): string;

    public function getExtension(): string;

    public function isImage(): bool;

    /**
     * @return Collection<int, FileTranslationInterface>
     */
    public function getTranslations(): Collection;

    public function addPage(PageInterface $page): void;

    public function addArticle(ArticleInterface $article): void;

    public function addObject(PageInterface|ArticleInterface $object): void;

    public function getReference(): string;

    /**
     * @return Collection<int, PageInterface>
    */
    public function getPages(): Collection;

    /**
     * @return Collection<int, ArticleInterface>
    */
    public function getArticles(): Collection;

    public function removePage(PageInterface $page): void;

    public function removeArticle(ArticleInterface $article): void;
    
    public function getTranslationByLocaleOrDefault(string $locale): FileTranslationInterface;

    /**
     * @return FileTranslationInterface
     */
    public function getTranslationByLocaleOrAny(string $locale);


    /**
     * @return ?FileTranslationInterface
     */
    public function getTranslationByLocale(string $locale);

    public function changeCopyright(string $copyright): void;

    public function getCopyright(): string;

    public function addTranslation(FileTranslationInterface $trans): void;

    public function changeFolder(MediaFolderInterface $folder): void;
}
