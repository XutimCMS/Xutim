<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\PublicationStatus;

interface TagTranslationInterface
{
    public function change(string $name, string $slug): void;

    public function getId(): Uuid;

    public function getTag(): TagInterface;

    public function getName(): string;

    public function getSlug(): string;

    public function getLocale(): string;

    public function getObject(): TagInterface;

    public function getStatus(): PublicationStatus;

    public function isInStatus(PublicationStatus $status): bool;

    public function isPublished(): bool;
}
