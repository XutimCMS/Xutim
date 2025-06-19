<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infra\Resolver;

use Symfony\Component\Routing\RouterInterface;
use Xutim\RedirectBundle\Domain\Model\RedirectInterface;
use Xutim\RedirectComponent\Domain\Model\RedirectInterface as ComponentRedirectInterface;
use Xutim\RedirectComponent\Domain\RedirectTargetResolverInterface;

class RedirectTargetResolver implements RedirectTargetResolverInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function resolveTargetUrl(ComponentRedirectInterface $redirect, ?string $locale = null): ?string
    {
        if (!$redirect instanceof RedirectInterface) {
            return null;
        }

        $trans = $redirect->getTargetContentTranslation();

        return $this->router->generate('content_translation_show', [
            'slug' => $trans->getSlug()
        ]);
    }
}
