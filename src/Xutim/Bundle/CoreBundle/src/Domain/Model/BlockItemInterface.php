<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Deprecated;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Form\Admin\Dto\ArticleBlockItemDto;
use Xutim\CoreBundle\Form\Admin\Dto\PageBlockItemDto;
use Xutim\CoreBundle\Form\Admin\Dto\SimpleBlockDto;

interface BlockItemInterface
{
    public function change(
        ?PageInterface $page,
        ?ArticleInterface $article,
        ?FileInterface $file,
        ?SnippetInterface $snippet,
        ?TagInterface $tag,
        ?string $link,
        ?string $colorHex,
        ?string $fileDescription,
        ?float $latitude,
        ?float $longitude
    ): void;

    public function getId(): Uuid;

    public function changePosition(int $position): void;

    public function getPosition(): int;

    public function hasFile(): bool;
    public function getFile(): ?FileInterface;
    public function getLink(): ?string;
    public function hasLink(): bool;
    public function getColor(): Color;
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

    public function isSimpleItem(): bool;

    public function getBlock(): BlockInterface;

    public function getCoordinates(): ?Coordinates;

    public function getDto(): PageBlockItemDto|ArticleBlockItemDto|SimpleBlockDto;

    public function changeFile(FileInterface $file): void;
}
