<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service\AdminEditUrl;

/**
 * Iterates registered `AdminEditUrlResolverInterface` services and
 * returns the first non-null URL. Returns an empty string if no
 * resolver handles the given entity — callers (twig helper) then omit
 * the "translate there" link rather than erroring.
 */
final readonly class AdminEditUrlResolver
{
    /**
     * @param iterable<AdminEditUrlResolverInterface> $resolvers
     */
    public function __construct(
        private iterable $resolvers,
    ) {
    }

    public function resolve(object $entity, string $locale): string
    {
        foreach ($this->resolvers as $resolver) {
            $url = $resolver->resolve($entity, $locale);
            if ($url !== null) {
                return $url;
            }
        }

        return '';
    }
}
