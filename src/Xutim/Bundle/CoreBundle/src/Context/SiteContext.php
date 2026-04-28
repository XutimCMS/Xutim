<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Xutim\CoreBundle\Dto\SiteDto;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\CoreBundle\Service\MenuBuilder;

class SiteContext
{
    public const string DEFAULT_SITE = 'default-site';
    public const string MAIN_MENU = 'main-menu';

    public function __construct(
        private readonly TagAwareCacheInterface $siteContextCache,
        private readonly SiteRepository $siteRepository,
        private readonly MenuBuilder $menuBuilder
    ) {
    }

    public function getDefaultSite(): SiteDto
    {
        $repo = $this->siteRepository;
        $siteDto = $this->siteContextCache->get(self::DEFAULT_SITE, function (ItemInterface $item) use ($repo) {
            $item->tag(['site']);
            $site = $repo->findOneBy([]);
            if ($site === null) {
                throw new \Exception('There is no default site configuration.');
            }

            return $site->toDto();
        });

        return $siteDto;
    }

    public function resetDefaultSite(): void
    {
        $this->siteContextCache->invalidateTags(['site']);
    }

    /**
     * @return array{
     *      roots: array<string>,
     *      items: array<string, array{
     *          children: list<string>,
     *          translations: array<string, array{name: string, route: string, hasLink: bool}>
     *      }>
     * }
     */
    public function getMenu(): array
    {
        return $this->siteContextCache->get(
            self::MAIN_MENU,
            function (ItemInterface $item) {
                $item->tag(['menu']);

                return $this->menuBuilder->buildMenu($this->getAllLocales());
            }
        );
    }

    public function resetMenu(): void
    {
        $this->siteContextCache->invalidateTags(['menu']);
    }

    /**
     * @return array<string>
     */
    public function getLocales(): array
    {
        return $this->getDefaultSite()->locales;
    }

    /**
     * @return array<string>
     */
    public function getMainLocales(): array
    {
        return $this->getDefaultSite()->locales;
    }

    /**
     * @return array<string>
     */
    public function getAllLocales(): array
    {
        return array_unique(
            array_merge(
                $this->getDefaultSite()->locales,
                $this->getDefaultSite()->extendedContentLocales
            )
        );
    }

    /**
     * @return array<string>
     */
    public function getExtendedContentLocales(): array
    {
        return $this->getDefaultSite()->extendedContentLocales;
    }

    /**
     * @param array<string> $usedLocales
     *
     * @return array<string>
     */
    public function getMissingExtendedLocales(array $usedLocales): array
    {
        $extendedLocales = $this->getDefaultSite()->extendedContentLocales;

        return array_values(array_diff($extendedLocales, $usedLocales));
    }

    public function getSender(): string
    {
        return $this->getDefaultSite()->sender;
    }

    /**
     * @return array<string>
     */
    public function getAdminAlertEmails(): array
    {
        return $this->getDefaultSite()->adminAlertEmails;
    }

    public function getReferenceLocale(): string
    {
        return $this->getDefaultSite()->referenceLocale;
    }

    public function getUntranslatedArticleAgeLimitDays(): int
    {
        return $this->getDefaultSite()->untranslatedArticleAgeLimitDays;
    }

    public function getHomepageId(): ?string
    {
        return $this->getDefaultSite()->homepageId;
    }
}
