<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

use Xutim\CoreBundle\Dto\SiteDto;

interface SiteInterface
{
    /**
     * @param array<string> $locales
     * @param array<string> $extendedContentLocales
     * @param array<string> $adminAlertEmails
     */
    public function change(
        array $locales,
        array $extendedContentLocales,
        string $theme,
        string $sender,
        string $referenceLocale,
        int $untranslatedArticleAgeLimitDays,
        ?PageInterface $homepage,
        array $adminAlertEmails = [],
    ): void;

    public function getHomepage(): ?PageInterface;

    public function changeHomepage(?PageInterface $homepage): void;

    public function getReferenceLocale(): string;

    /**
     * @return array<string>
     */
    public function getLocales(): array;

    /**
     * @return array<string>
     */
    public function getContentLocales(): array;

    public function getSender(): string;

    /**
     * @return array<string>
     */
    public function getAdminAlertEmails(): array;

    public function toDto(): SiteDto;
}
