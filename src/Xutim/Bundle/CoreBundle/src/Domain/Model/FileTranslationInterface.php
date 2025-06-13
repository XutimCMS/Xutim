<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;

interface FileTranslationInterface
{
    public function __toString(): string;

    public function update(string $name, string $alt): void;

    public function getId(): Uuid;

    public function getName(): string;

    public function getAlt(): string;

    public function getFile(): FileInterface;

    public function getLocale(): string;
}
