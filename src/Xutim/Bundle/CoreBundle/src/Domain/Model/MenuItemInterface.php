<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Form\Admin\Dto\MenuItemDto;

interface MenuItemInterface
{
    public function change(
        bool $hasLink,
        ?PageInterface $page,
        ?ArticleInterface $article,
        ?PageInterface $overwritePage,
        ?SnippetInterface $snippetAnchor
    ): void;

    public function getId(): Uuid;

    public function hasLink(): bool;

    public function getParent(): ?MenuItemInterface;

    public function getPage(): ?PageInterface;

    public function getObject(): PageInterface|ArticleInterface;

    public function getObjectTranslation(?string $locale): ContentTranslationInterface;

    public function getPageTranslation(?string $locale): ContentTranslationInterface;

    public function getArticleTranslation(?string $locale): ContentTranslationInterface;

    public function getArticle(): ?ArticleInterface;

    public function getPosition(): int;

    public function movePosUp(int $step): void;

    public function movePosDown(int $step): void;

    public function hasArticle(): bool;

    public function hasPage(): bool;

    public function ovewritesPage(): bool;

    public function getOverwritePage(): ?PageInterface;

    public function hasSnippetAnchor(): bool;

    public function getSnippetAnchor(): ?SnippetInterface;

    public function toDto(): MenuItemDto;
}
