<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;

interface RedirectInterface
{
    public function change(
        string $source,
        string $target,
        bool $permanent = false,
    ): void;

    public function getId(): Uuid;

    public function getTarget(): string;

    public function getSource(): string;

    public function isPermanent(): bool;
}
