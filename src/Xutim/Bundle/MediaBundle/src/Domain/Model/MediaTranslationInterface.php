<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;

interface MediaTranslationInterface
{
    public function id(): Uuid;

    public function media(): MediaInterface;

    public function locale(): string;

    public function name(): string;

    public function alt(): string;

    public function change(string $name, string $alt): void;
}
