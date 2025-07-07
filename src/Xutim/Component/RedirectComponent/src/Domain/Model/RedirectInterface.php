<?php

declare(strict_types=1);

namespace Xutim\RedirectComponent\Domain\Model;

use Symfony\Component\Uid\Uuid;

interface RedirectInterface
{
    public function getId(): Uuid;

    public function getSource(): string;

    public function getTarget(): string;

    public function isPermanent(): bool;
}
