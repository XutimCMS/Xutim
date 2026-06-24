<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Model\LocaleAwareInterface;

interface SnippetTranslationInterface extends LocaleAwareInterface
{
    public function getId(): Uuid;

    public function getSnippet(): SnippetInterface;

    public function getContent(): string;

    public function getLocale(): string;

    public function update(string $content): void;
}
