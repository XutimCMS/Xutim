<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\Color;

interface BlockInterface
{
    public function change(string $code, string $name, string $description, string $layout): void;

    public function getId(): Uuid;

    public function getCode(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function getLayout(): string;

    public function simpleItemRandomizedByWeek(): ?BlockItemInterface;

    public function getColor(): Color;

    /**
     * @return ReadableCollection<int, BlockItemInterface>
     */
    public function getObjectBlockItemsByLocale(string $locale): ReadableCollection;

    /**
     * @return Collection<int, BlockItemInterface>
     */
    public function getBlockItems(): Collection;

    /**
     * @return ReadableCollection<int, BlockItemInterface>
     */
    public function getPagesItems(): ReadableCollection;

    /**
     * @return ReadableCollection<int, BlockItemInterface>
     */
    public function getArticlesItems(): ReadableCollection;

    /**
     * @return ReadableCollection<int, BlockItemInterface>
     */
    public function getSimpleItems(): ReadableCollection;

    public function addItem(BlockItemInterface $item): void;

    public function updates(): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
