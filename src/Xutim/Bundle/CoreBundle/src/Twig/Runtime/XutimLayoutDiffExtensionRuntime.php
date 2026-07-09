<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Config\Layout\Block\Option\RichTextBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Service\AdminEditUrl\AdminEditUrlResolver;
use Xutim\CoreBundle\Service\XutimLayoutValueResolver;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;

/**
 * Presents raw xutimLayout field values as display structs for the
 * revision diff view: reference UUIDs resolve to entity titles with
 * admin edit links, media to thumbnails, unions and collections to
 * their member displays. Scalars pass through as text.
 *
 * Display struct shape consumed by xutim_layout_diff.html.twig:
 *   kind:      empty|text|url|html|entity|image|file|missing|list
 *   text:      label to show (entity title, scalar value, …)
 *   editUrl:   admin edit link when the entity resolves to one
 *   media:     MediaInterface for image/file kinds (thumbnail source)
 *   typeLabel: short entity-type tag (Page, Article, Tag, …)
 *   items:     nested displays for collection fields
 */
class XutimLayoutDiffExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly LayoutDefinitionRegistry $registry,
        private readonly XutimLayoutValueResolver $resolver,
        private readonly AdminEditUrlResolver $adminEditUrlResolver,
        private readonly XutimLayoutExtensionRuntime $layoutRuntime,
        private readonly SiteContext $siteContext,
    ) {
    }

    public function layoutName(string $code): string
    {
        return $this->registry->getByCode($code)?->getName() ?? $code;
    }

    /**
     * @return array<string, mixed>
     */
    public function fieldDisplay(string $layoutCode, string $fieldName, mixed $raw, string $locale): array
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return ['kind' => 'empty', 'text' => ''];
        }

        $definition = $this->registry->getByCode($layoutCode);
        $option = $definition?->getFields()[$fieldName] ?? null;

        if ($definition === null || $option === null) {
            return $this->scalarDisplay($raw);
        }

        if ($option instanceof RichTextBlockItemOption) {
            if (is_array($raw)) {
                return ['kind' => 'html', 'text' => $this->layoutRuntime->richTextHtml($raw)];
            }

            return $this->scalarDisplay($raw);
        }

        $resolved = $this->resolver->resolve($definition, [$fieldName => $raw])[$fieldName] ?? null;

        return $this->displayValue($resolved, $raw, $locale);
    }

    /**
     * @return array<string, mixed>
     */
    private function displayValue(mixed $resolved, mixed $raw, string $locale): array
    {
        if ($resolved instanceof MediaInterface) {
            return $this->withEditUrl([
                'kind' => $resolved->isImage() ? 'image' : 'file',
                'text' => $this->mediaLabel($resolved),
                'media' => $resolved,
            ], $resolved, $locale);
        }

        if ($resolved instanceof PageInterface) {
            return $this->entityDisplay($resolved, $this->contentTitle($resolved, $locale), 'Page', $locale);
        }

        if ($resolved instanceof ArticleInterface) {
            return $this->entityDisplay($resolved, $this->contentTitle($resolved, $locale), 'Article', $locale);
        }

        if ($resolved instanceof TagInterface) {
            return $this->entityDisplay($resolved, $this->tagTitle($resolved, $locale), 'Tag', $locale);
        }

        if ($resolved instanceof SnippetInterface) {
            return $this->entityDisplay($resolved, $resolved->getCode(), 'Block', $locale);
        }

        if ($resolved instanceof MediaFolderInterface) {
            return $this->entityDisplay($resolved, $resolved->name(), 'Folder', $locale);
        }

        if (is_array($resolved) && array_key_exists('entity', $resolved)) {
            if ($resolved['entity'] !== null) {
                return $this->displayValue($resolved['entity'], $resolved['value'] ?? null, $locale);
            }

            $value = $resolved['value'] ?? null;

            return is_scalar($value) && (string) $value !== ''
                ? $this->scalarDisplay($value)
                : ['kind' => 'empty', 'text' => ''];
        }

        if (is_array($resolved) && array_is_list($resolved)) {
            $items = [];
            foreach ($resolved as $item) {
                $items[] = $this->displayValue($item, null, $locale);
            }

            return ['kind' => 'list', 'text' => '', 'items' => $items];
        }

        if ($resolved === null) {
            return is_string($raw) && $raw !== ''
                ? ['kind' => 'missing', 'text' => $raw]
                : ['kind' => 'empty', 'text' => ''];
        }

        return $this->scalarDisplay($resolved);
    }

    /**
     * @return array<string, mixed>
     */
    private function scalarDisplay(mixed $value): array
    {
        if (is_bool($value)) {
            return ['kind' => 'text', 'text' => $value ? 'true' : 'false'];
        }

        if (is_scalar($value)) {
            $text = (string) $value;

            return [
                'kind' => preg_match('~^https?://~', $text) === 1 ? 'url' : 'text',
                'text' => $text,
            ];
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return ['kind' => 'text', 'text' => is_string($json) ? $json : ''];
    }

    /**
     * @return array<string, mixed>
     */
    private function entityDisplay(object $entity, string $title, string $typeLabel, string $locale): array
    {
        return $this->withEditUrl([
            'kind' => 'entity',
            'text' => $title !== '' ? $title : '(untitled)',
            'typeLabel' => $typeLabel,
        ], $entity, $locale);
    }

    /**
     * @param array<string, mixed> $display
     * @return array<string, mixed>
     */
    private function withEditUrl(array $display, object $entity, string $locale): array
    {
        $url = $this->adminEditUrlResolver->resolve($entity, $locale);
        if ($url !== '') {
            $display['editUrl'] = $url;
        }

        return $display;
    }

    private function contentTitle(PageInterface|ArticleInterface $entity, string $locale): string
    {
        return $entity->getTranslationByLocaleOrAny($locale, $this->siteContext->getReferenceLocale())->getTitle();
    }

    private function tagTitle(TagInterface $tag, string $locale): string
    {
        return $tag->getTranslationByLocaleOrAny($locale)->getName();
    }

    private function mediaLabel(MediaInterface $media): string
    {
        $name = $media->innerName();

        return $name !== '' ? $name : basename($media->originalPath());
    }
}
