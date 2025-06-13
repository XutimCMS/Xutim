<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Form\Admin\Dto\SnippetDto;

interface SnippetInterface
{
    public function __toString(): string;

    public function getId(): Uuid;

    public function getCode(): string;

    public function isRouteType(): bool;

    public function change(string $code): void;

    /**
     * @return Collection<int, SnippetTranslationInterface>
     */
    public function getTranslations(): Collection;

    public function addTranslation(SnippetTranslationInterface $translation): void;

    public function toDto(): SnippetDto;

    /**
     * @return ?SnippetTranslationInterface
     */
    public function getTranslationByLocale(string $locale);

    /**
     * @return SnippetTranslationInterface
     */
    public function getTranslationByLocaleOrAny(string $locale);
}
