<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infrastructure\Resolver;

use Symfony\Component\Routing\RouterInterface;
use Xutim\RedirectBundle\Domain\Model\CmsRedirectInterface;
use Xutim\RedirectComponent\Domain\Model\RedirectInterface;
use Xutim\RedirectComponent\Domain\RedirectTargetResolverInterface;

class CmsRedirectTargetResolver implements RedirectTargetResolverInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function resolveTargetUrl(RedirectInterface $redirect, ?string $locale = null): ?string
    {
        if (!$redirect instanceof CmsRedirectInterface) {
            return null;
        }

        $trans = $redirect->getTargetContentTranslation();

        return $this->router->generate('show_content_translation', [
            'slug' => $trans->getSlug()
        ]);
    }
}
