<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service\AdminEditUrl;

/**
 * Resolves an entity to the URL of its admin edit screen for a given
 * locale, used by the xutim-layout preview to let translators jump
 * from a referenced entity in a block to that entity's own editor.
 *
 * Multiple resolvers can be registered; the registry dispatches to the
 * first one that returns a non-null URL. Downstream bundles tag their
 * own resolvers to extend coverage (e.g. Song, Event, custom types).
 */
interface AdminEditUrlResolverInterface
{
    /**
     * Returns the admin edit URL for `$entity` (locale-scoped where the
     * entity supports translations), or `null` if this resolver does
     * not handle the given type.
     */
    public function resolve(object $entity, string $locale): ?string;
}
