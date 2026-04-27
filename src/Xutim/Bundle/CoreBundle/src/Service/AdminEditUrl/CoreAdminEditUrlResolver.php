<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service\AdminEditUrl;

use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

/**
 * Covers the built-in entity types shipped by Xutim core and the
 * directly-coupled SnippetBundle / MediaBundle. Downstream bundles can
 * register additional resolvers for their own entities via the
 * `xutim.admin_edit_url_resolver` tag.
 */
final readonly class CoreAdminEditUrlResolver implements AdminEditUrlResolverInterface
{
    public function __construct(
        private AdminUrlGenerator $router,
    ) {
    }

    public function resolve(object $entity, string $locale): ?string
    {
        if ($entity instanceof PageInterface) {
            return $this->router->generate('admin_page_edit', [
                'id' => $entity->getId()->toRfc4122(),
                'locale' => $locale,
            ]);
        }

        if ($entity instanceof ArticleInterface) {
            return $this->router->generate('admin_article_edit', [
                'id' => $entity->getId()->toRfc4122(),
                'locale' => $locale,
            ]);
        }

        if ($entity instanceof TagInterface) {
            return $this->router->generate('admin_tag_edit', [
                'id' => $entity->getId()->toRfc4122(),
                'locale' => $locale,
            ]);
        }

        if ($entity instanceof SnippetInterface) {
            return $this->router->generate('admin_snippet_edit', [
                'id' => $entity->getId()->toRfc4122(),
            ]);
        }

        if ($entity instanceof MediaInterface) {
            return $this->router->generate('admin_media_edit', [
                'id' => $entity->id()->toRfc4122(),
            ]);
        }

        if ($entity instanceof MediaFolderInterface) {
            return $this->router->generate('admin_media_folder_edit', [
                'id' => $entity->id()->toRfc4122(),
            ]);
        }

        return null;
    }
}
