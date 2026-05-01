<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Block\Option\ArticleBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionCollection;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOptionUnion;
use Xutim\CoreBundle\Config\Layout\Block\Option\FileBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\ImageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\MediaFolderBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageOrArticleBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\SnippetBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TagBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\SnippetBundle\Domain\Repository\SnippetRepositoryInterface;

/**
 * Walks a xutimLayout `values` blob and eager-loads UUID reference
 * fields (image, page, article, tag, snippet, media folder) into their
 * entity counterparts for use inside twig templates. Scalar fields
 * pass through unchanged.
 *
 * Missing entities resolve to null — templates must tolerate null refs.
 */
final readonly class XutimLayoutValueResolver
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private MediaFolderRepositoryInterface $mediaFolderRepository,
        private PageRepository $pageRepository,
        private ArticleRepository $articleRepository,
        private TagRepository $tagRepository,
        private SnippetRepositoryInterface $snippetRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $storedValues
     * @return array<string, mixed>
     */
    public function resolve(LayoutDefinition $definition, array $storedValues): array
    {
        $resolved = [];

        foreach ($definition->getFields() as $fieldName => $option) {
            $raw = $storedValues[$fieldName] ?? null;
            $resolved[$fieldName] = $this->resolveField($option, $raw);
        }

        return $resolved;
    }

    private function resolveField(BlockItemOption $option, mixed $raw): mixed
    {
        if ($option instanceof BlockItemOptionCollection) {
            return $this->resolveCollection($option, $raw);
        }

        if ($option instanceof BlockItemOptionUnion) {
            return $this->resolveUnion($option, $raw);
        }

        if ($option instanceof PageOrArticleBlockItemOption) {
            return $this->resolvePageOrArticle($raw);
        }

        if ($option instanceof ImageBlockItemOption || $option instanceof FileBlockItemOption) {
            return $this->resolveUuid($raw, fn (Uuid $id) => $this->mediaRepository->findById($id));
        }

        if ($option instanceof MediaFolderBlockItemOption) {
            return $this->resolveUuid($raw, fn (Uuid $id) => $this->mediaFolderRepository->findById($id));
        }

        if ($option instanceof PageBlockItemOption) {
            return $this->resolveStringId($raw, fn (string $id) => $this->pageRepository->find($id));
        }

        if ($option instanceof ArticleBlockItemOption) {
            return $this->resolveStringId($raw, fn (string $id) => $this->articleRepository->find($id));
        }

        if ($option instanceof TagBlockItemOption) {
            return $this->resolveStringId($raw, fn (string $id) => $this->tagRepository->find($id));
        }

        if ($option instanceof SnippetBlockItemOption) {
            return $this->resolveStringId($raw, fn (string $id) => $this->snippetRepository->findById($id));
        }

        return $raw;
    }

    /**
     * Each collection item is an associative array. For unions the
     * shape is `{type, value}` and we return `{type, value, entity}`
     * where `entity` is the hydrated reference (or null). For scalar
     * inner options, we return the hydrated single entity directly.
     *
     * @return list<mixed>
     */
    private function resolveCollection(BlockItemOptionCollection $option, mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $inner = $option->getOption();
        $items = [];
        foreach ($raw as $item) {
            $items[] = $this->resolveCollectionItem($inner, $item);
        }

        return $items;
    }

    private function resolveCollectionItem(BlockItemOption $innerOption, mixed $item): mixed
    {
        if ($innerOption instanceof BlockItemOptionUnion) {
            return $this->resolveUnion($innerOption, $item);
        }

        if (!is_array($item)) {
            return null;
        }

        return $this->resolveField($innerOption, $item['value'] ?? null);
    }

    /**
     * @return array{type: string, value: mixed, entity: mixed}|null
     */
    private function resolveUnion(BlockItemOptionUnion $option, mixed $raw): ?array
    {
        if (!is_array($raw)) {
            return null;
        }

        $type = is_string($raw['type'] ?? null) ? $raw['type'] : null;
        $value = $raw['value'] ?? null;
        if ($type === null) {
            return null;
        }

        $memberOption = null;
        foreach ($option->getDecomposedOptions() as $candidate) {
            if ($this->shortName($candidate::class) === $type) {
                $memberOption = $candidate;
                break;
            }
        }

        if ($memberOption === null) {
            return ['type' => $type, 'value' => $value, 'entity' => null];
        }

        return [
            'type' => $type,
            'value' => $value,
            'entity' => $this->resolveField($memberOption, $value),
        ];
    }

    /**
     * @return array{type: 'page'|'article', value: string, entity: mixed}|null
     */
    private function resolvePageOrArticle(mixed $raw): ?array
    {
        if (!is_array($raw)) {
            return null;
        }

        $type = $raw['type'] ?? null;
        $value = $raw['value'] ?? null;
        if (!is_string($type) || !is_string($value) || $value === '') {
            return null;
        }

        $normalized = match ($type) {
            'page', 'PageBlockItemOption' => 'page',
            'article', 'ArticleBlockItemOption' => 'article',
            default => null,
        };
        if ($normalized === null) {
            return null;
        }

        $entity = $normalized === 'page'
            ? $this->resolveStringId($value, fn (string $id) => $this->pageRepository->find($id))
            : $this->resolveStringId($value, fn (string $id) => $this->articleRepository->find($id));

        return ['type' => $normalized, 'value' => $value, 'entity' => $entity];
    }

    private function shortName(string $fqcn): string
    {
        $pos = strrpos($fqcn, '\\');

        return $pos === false ? $fqcn : substr($fqcn, $pos + 1);
    }

    /**
     * @param callable(Uuid): mixed $loader
     */
    private function resolveUuid(mixed $raw, callable $loader): mixed
    {
        if (!is_string($raw) || $raw === '' || !Uuid::isValid($raw)) {
            return null;
        }

        try {
            return $loader(Uuid::fromString($raw));
        } catch (\Throwable $e) {
            $this->logger->warning('xutim layout value resolver failed to load entity', [
                'uuid' => $raw,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param callable(string): mixed $loader
     */
    private function resolveStringId(mixed $raw, callable $loader): mixed
    {
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        try {
            return $loader($raw);
        } catch (\Throwable $e) {
            $this->logger->warning('xutim layout value resolver failed to load entity', [
                'id' => $raw,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
