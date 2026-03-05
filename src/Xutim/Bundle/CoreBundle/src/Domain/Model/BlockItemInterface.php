<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Deprecated;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

interface BlockItemInterface
{
    /**
     * @param array<string, mixed> $extra
     */
    public function change(
        ?PageInterface $page,
        ?ArticleInterface $article,
        ?MediaInterface $file,
        ?SnippetInterface $snippet,
        ?TagInterface $tag,
        ?MediaFolderInterface $folder,
        ?string $text,
        ?string $link,
        ?string $fileDescription,
        array $extra = [],
    ): void;

    public function getId(): Uuid;

    public function changePosition(int $position): void;

    public function getPosition(): int;

    public function hasFile(): bool;
    public function getFile(): ?MediaInterface;
    public function getText(): ?string;
    public function hasText(): bool;
    public function getLink(): ?string;
    public function hasLink(): bool;
    public function getFileDescription(): ?string;
    public function getObject(): PageInterface|ArticleInterface|null;

    #[Deprecated("use hasContentObject() instead")]
    public function hasObject(): bool;

    public function hasContentObject(): bool;

    public function getPage(): ?PageInterface;

    public function getArticle(): ?ArticleInterface;

    public function hasPage(): bool;

    public function hasArticle(): bool;

    public function getSnippet(): ?SnippetInterface;

    public function hasSnippet(): bool;

    public function getTag(): ?TagInterface;

    public function hasTag(): bool;

    public function getMediaFolder(): ?MediaFolderInterface;

    public function hasMediaFolder(): bool;

    public function isSimpleItem(): bool;

    public function getBlock(): BlockInterface;

    /**
     * @return array<string, mixed>
     */
    public function getExtra(): array;

    public function getDto(): BlockItemDto;

    public function changeFile(MediaInterface $file): void;
}
