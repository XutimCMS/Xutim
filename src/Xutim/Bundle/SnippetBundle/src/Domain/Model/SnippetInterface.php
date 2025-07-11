<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Model;

use Symfony\Component\Uid\Uuid;
use Xutim\SnippetBundle\Context\SnippetData;
use Xutim\SnippetBundle\Form\SnippetFormData;

interface SnippetInterface
{
    public function change(string $code, string $description, SnippetCategory $category): void;

    public function getId(): Uuid;

    public function getCode(): string;

    public function getDescription(): string;

    public function getCategory(): SnippetCategory;

    public function isRouteType(): bool;

    /**
     * @return array<int, SnippetTranslationInterface>
    */
    public function getTranslations(): array;

    public function addTranslation(SnippetTranslationInterface $translation): void;

    public function toFormData(): SnippetFormData;

    public function toData(): SnippetData;

    /**
     * @return ?SnippetTranslationInterface
     */
    public function getTranslationByLocale(string $locale);

    /**
     * @return SnippetTranslationInterface
     */
    public function getTranslationByLocaleOrAny(string $locale);
}
