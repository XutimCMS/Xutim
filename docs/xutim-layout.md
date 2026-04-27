# Xutim Layout

A xutim layout is a reusable content block. Authors insert it into editor.js content via the `xutimLayout` tool. Each layout declares its own fields and twig template. Values are stored inline in the editor.js JSON.

## How It Works

1. A class implementing `LayoutDefinition` declares a `code`, a `name`, and a map of `fieldName => BlockItemOption` describing its typed fields
2. The admin layout picker lists every registered definition; selecting one renders an inline form with one input per field, dispatching to the matching `LayoutFieldProvider`
3. Submitted values land in the editor.js JSON as `{layoutCode, values: {fieldName: storedValue}}`
4. On render, `XutimLayoutValueResolver` walks the values and eager-loads UUID/string-ID references (image, page, article, tag, snippet, media folder) into their entities; scalar fields pass through unchanged
5. `render_xutim_layout` renders the definition's twig template with `{layout, values}`
6. Definitions are auto-tagged with `xutim.layout_definition` via `autoconfigure` and collected by `LayoutDefinitionRegistry`

## Creating a Layout

### 1. Definition Class

Implements `LayoutDefinition`. Reuse existing `BlockItemOption` types from `Xutim\CoreBundle\Config\Layout\Block\Option\` for fields. They decide form widget, storage shape, translatability, and entity resolution.

```php
<?php

declare(strict_types=1);

namespace App\Config\Layout;

use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\ImageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinition;

final readonly class HeroLayoutDefinition implements LayoutDefinition
{
    public function getCode(): string
    {
        return 'hero';
    }

    public function getName(): string
    {
        return 'Hero';
    }

    /**
     * @return array<string, BlockItemOption>
     */
    public function getFields(): array
    {
        return [
            'title' => new TextBlockItemOption(),
            'subtitle' => new TextBlockItemOption(),
            'background' => new ImageBlockItemOption(),
        ];
    }

    public function getFieldDescriptions(): array
    {
        return [
            'title' => 'Main heading shown over the hero image.',
        ];
    }

    public function getTemplate(): string
    {
        return 'layout/hero/hero.html.twig';
    }

    public function getFormBodyTemplate(): ?string
    {
        return null;
    }

    public function getDescription(): string
    {
        return 'Full-bleed hero with title, subtitle, and background.';
    }

    public function getCategory(): string
    {
        return 'Headers';
    }

    public function getPreviewImage(): string
    {
        return '/static/layout-previews/hero.png';
    }
}
```

### 2. Twig Template

Rendered with `layout` (the `LayoutDefinition`), `values` (resolved field values), and `editable` (true only inside the admin preview iframe). Wrap each editable text field with `xutim_editable()` so translators can edit it inline. Reference fields arrive as entities or null, so templates must tolerate null.

```twig
{# templates/layout/hero/hero.html.twig #}

<section class="hero">
    {% if values.background %}
        <img src="{{ media_url(values.background, 'hero') }}" alt="">
    {% endif %}
    <h1>{{ xutim_editable('title', values.title) }}</h1>
    <p>{{ xutim_editable('subtitle', values.subtitle) }}</p>
</section>
```

### 3. Registration

The class is auto-tagged via `xutim.layout_definition`. It immediately appears in the editor.js layout picker under its category, with form fields driven by the field options.

## Field Types

Pick the option that matches the data shape. The matching `LayoutFieldProvider` wires up the form widget and the value resolver hydrates references on render.

| Option                          | Stored as                  | Twig receives                |
|---------------------------------|----------------------------|------------------------------|
| `TextBlockItemOption`           | string                     | string                       |
| `TextareaBlockItemOption`       | string                     | string                       |
| `RichTextBlockItemOption`       | TipTap node array          | array (use `richTextHtml()`) |
| `ImageBlockItemOption`          | UUID                       | `Media` or null              |
| `FileBlockItemOption`           | UUID                       | `Media` or null              |
| `MediaFolderBlockItemOption`    | UUID                       | `MediaFolder` or null        |
| `PageBlockItemOption`           | string id                  | `Page` or null               |
| `ArticleBlockItemOption`        | string id                  | `Article` or null            |
| `TagBlockItemOption`            | string id                  | `Tag` or null                |
| `SnippetBlockItemOption`        | string id                  | `Snippet` or null            |
| `LinkBlockItemOption`           | string                     | string                       |
| `PageOrArticleBlockItemOption`  | `{type, value}`            | `{type, value, entity}`      |
| `BlockItemOptionCollection`     | list of inner option items | list, each resolved          |
| `BlockItemOptionUnion`          | `{type, value}`            | `{type, value, entity}`      |

## Custom Form Body

Return a twig path from `getFormBodyTemplate()` to override the linear field list with a bespoke arrangement (e.g. a grid that mirrors the public render). The template receives `form`, `definition`, and `descriptions`. Use the `@XutimCore/admin/xutim_layout/_fields.html.twig` macros (`xl.scalar`, `xl.image`, `xl.collection`, `xl.union`) so each field renders with the right widget.

## Layout vs. Page/Article/Block Layouts

Don't confuse a xutim layout with the article/page/block/tag layouts under `templates/themes/<theme>/layout/`. Those are **whole-page templates** picked per content entity and configured via `LayoutConfig` returned from a `config.php` file. A xutim layout is an **inline content fragment** registered as a PHP service and dropped into editor.js content alongside paragraphs and images.
