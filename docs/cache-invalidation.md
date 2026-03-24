# Cache Invalidation with Tags

Xutim uses Symfony's `TagAwareCacheInterface` to automatically invalidate caches when content changes.

## How It Works

### Tagging at write time

When a block is cached, `BlockContext` automatically tags the cache entry with its dependencies:

- `block.{code}` — the block itself
- `article.{id}` — for each article in BlockItems
- `page.{id}` — for each page in BlockItems
- `tag.{id}` — for each tag in BlockItems
- `media.{id}` — for each media file in BlockItems
- `snippet.{code}` — for each snippet in BlockItems
- `snippet.{code}` — for each snippet accessed via `render_snippet()` during rendering (tracked automatically)
- `mediafolder.{id}` — for each media folder in BlockItems

### Automatic invalidation (core)

`CacheInvalidationListener` (Doctrine listener) fires on every entity persist/update/remove and invalidates the relevant tags:

| Entity changed | Tags invalidated |
|---|---|
| Article | `article.{id}`, `tag.{tagId}` for each tag |
| Page | `page.{id}`, `menu`, `page_tree` |
| ContentTranslation | Parent `article.{id}` or `page.{id}`, `menu` |
| Tag | `tag.{id}` |
| Media | `media.{id}` |
| SnippetTranslation | `snippet.{code}` |
| BlockItem | `block.{parentBlockCode}` |
| Block | `block.{code}` |
| MenuItem | `menu` |
| Site | `site` |

The article→tag cascade ensures that when an article is published or changed, blocks showing "latest articles by tag X" are invalidated.

## Application-Specific Cascades

The core listener handles direct entity→tag mappings. However, block templates often traverse **relationships beyond direct BlockItem references**. These cascades are application-specific and must be handled by the application.

### When you need a cascade listener

If your block template does any of the following, you need an application-level Doctrine listener:

1. **Traverses children**: `page.children`, `folder.media()`, `tag.articles`
2. **Queries related entities**: custom Twig functions that load entities by relationship
3. **Accesses transitive properties**: `blockItem.article.featuredImage.copyright`

### Example: page children

A block template that lists child pages:

```twig
{% set parentPage = block.blockItems|first.page %}
{% for page in parentPage.children %}
    {{ page.translationByLocale(app.locale).title }}
{% endfor %}
```

The block cache is tagged with `page.{parentPageId}` but NOT with each child page's ID. When a child page changes, the block won't invalidate.

**Fix**: Add a Doctrine listener in your application:

```php
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
final class AppCacheInvalidationListener
{
    public function __construct(private readonly CacheTagInvalidator $invalidator) {}

    // ... postPersist/postUpdate/postRemove methods calling invalidate()

    private function invalidate(object $entity): void
    {
        $tags = match (true) {
            $entity instanceof PageInterface => $this->resolvePageCascade($entity),
            default => [],
        };

        if ($tags !== []) {
            $this->invalidator->invalidateTags($tags);
        }
    }

    private function resolvePageCascade(PageInterface $page): array
    {
        $parent = $page->getParent();
        if ($parent === null) {
            return [];
        }
        // Child changed → invalidate parent so blocks iterating children refresh
        return ['page.' . $parent->getId()];
    }
}
```

### Example: media folder

A block template that displays images from a folder:

```twig
{% for media in block.blockItems|first.mediaFolder.media %}
    {{ media_url(media.originalPath) }}
{% endfor %}
```

The block is tagged with `mediafolder.{folderId}`. When new media is added to the folder, the application listener should invalidate:

```php
$entity instanceof MediaInterface => $this->resolveMediaCascade($entity),

private function resolveMediaCascade(MediaInterface $media): array
{
    $folder = $media->folder();
    return $folder !== null ? ['mediafolder.' . $folder->id()] : [];
}
```

### Snippet tracking

Snippets used via `render_snippet()` in block templates are tracked automatically at render time using `SnippetUsageTracker`. No application configuration needed — when a snippet changes, only blocks that actually rendered that snippet are invalidated.

## Debugging

To force-clear all block caches: `php bin/console cache:pool:clear block_context.cache`
