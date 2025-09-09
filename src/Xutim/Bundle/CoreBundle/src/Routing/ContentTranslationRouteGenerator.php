<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

class ContentTranslationRouteGenerator
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly SiteContext $siteContext,
        private readonly string $defaultLocale
    ) {
    }

    /**
     * @param array<string, string> $params
     */
    public function generatePath(
        ContentTranslationInterface $trans,
        ?string $mainLocale,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
        array $params = []
    ): string {
        if (!in_array($mainLocale, $this->siteContext->getMainLocales(), true)) {
            $mainLocale = $this->defaultLocale;
        }

        $params = array_merge([
            '_locale' => $mainLocale,
            'slug' => $trans->getSlug()
        ], $params);

        if ($mainLocale !== $trans->getLocale()) {
            if (in_array($trans->getLocale(), $this->siteContext->getMainLocales(), true)) {
                $params['_locale'] = $trans->getLocale();
            } else {
                $params['_content_locale'] = $trans->getLocale();
            }
        }

        return $this->router->generate('content_translation_show', $params, $referenceType);
    }
}
