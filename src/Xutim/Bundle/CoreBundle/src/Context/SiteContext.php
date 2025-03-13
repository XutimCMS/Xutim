<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Context;

use Symfony\Contracts\Cache\CacheInterface;
use Xutim\CoreBundle\Dto\SiteDto;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\CoreBundle\Service\MenuBuilder;

class SiteContext
{
    public const string DEFAULT_SITE = 'default-site';
    public const string MAIN_MENU = 'main-menu';

    public function __construct(
        private readonly CacheInterface $siteContextCache,
        private readonly SiteRepository $siteRepository,
        private readonly MenuBuilder $menuBuilder
    ) {
    }

    public function getDefaultSite(): SiteDto
    {
        $repo = $this->siteRepository;
        $siteDto = $this->siteContextCache->get(self::DEFAULT_SITE, function () use ($repo) {
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
        $this->siteContextCache->delete(self::DEFAULT_SITE);
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
        return $this->siteContextCache->get(self::MAIN_MENU, fn () => $this->menuBuilder->buildMenu());
    }

    public function resetMenu(): void
    {
        $this->siteContextCache->delete(self::MAIN_MENU);
    }

    /**
     * @return array<string>
     */
    public function getLocales(): array
    {
        return $this->getDefaultSite()->locales;
    }

    public function getSender(): string
    {
        return $this->getDefaultSite()->sender;
    }
}
