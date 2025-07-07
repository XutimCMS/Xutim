<?php

declare(strict_types=1);

namespace Xutim\RedirectComponent\Infra;

use Xutim\RedirectComponent\Domain\Model\RedirectInterface;
use Xutim\RedirectComponent\Domain\RedirectTargetResolverInterface;

class RedirectResolver
{
    /**
     * @param iterable<RedirectTargetResolverInterface> $resolvers
     */
    public function __construct(
        private iterable $resolvers
    ) {
    }

    public function resolve(RedirectInterface $redirect): ?string
    {
        foreach ($this->resolvers as $resolver) {
            $url = $resolver->resolveTargetUrl($redirect);
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }
}
