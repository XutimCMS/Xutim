<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Model;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

interface MediaVariantInterface
{
    public function id(): Uuid;

    public function media(): MediaInterface;

    public function preset(): string;

    public function format(): string;

    public function width(): int;

    public function height(): int;

    public function path(): string;

    public function fingerprint(): string;

    public function createdAt(): DateTimeImmutable;
}
