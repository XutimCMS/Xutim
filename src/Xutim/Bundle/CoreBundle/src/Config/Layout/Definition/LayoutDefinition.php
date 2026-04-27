<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Definition;

use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;

/**
 * Defines an inline "compound layout" available to editor.js content via
 * the `xutimLayout` block type. Each definition declares its typed fields,
 * the twig template to render them, and a stable code used to look it up.
 *
 * Field values are stored inline per-locale inside the editor.js JSON
 * blob on `ContentTranslation.content`, not in a separate DB table.
 */
interface LayoutDefinition
{
    /**
     * Stable slug used as the persisted identifier for the layout,
     * e.g. `"page-preview"`. Must be unique across the project.
     */
    public function getCode(): string;

    /**
     * Human-readable label shown in the admin layout picker.
     */
    public function getName(): string;

    /**
     * Field name → option mapping. The field name is the key used
     * in the persisted `values` map. Declaration order defines form
     * field order and preview fallback order.
     *
     * @return array<string, BlockItemOption>
     */
    public function getFields(): array;

    /**
     * Optional help text shown under each form field in the admin.
     * Keyed by field name. Fields without an entry show no description.
     *
     * @return array<string, string>
     */
    public function getFieldDescriptions(): array;

    /**
     * Twig template path rendered by `render_xutim_layout` with
     * `{layout, values}` context.
     */
    public function getTemplate(): string;

    /**
     * Optional twig template path rendered as the BODY of the admin
     * edit form — i.e. the field arrangement only (no wrapper, no
     * save/cancel buttons, no CSRF). Lets layouts adopt a bespoke
     * form shape that mirrors their public render (e.g. grid columns
     * matching their rendered layout). Return null to use the default
     * linear field list.
     *
     * Context passed: `form`, `definition`, `descriptions` (the result
     * of `getFieldDescriptions()`).
     */
    public function getFormBodyTemplate(): ?string;

    /**
     * Short one-liner shown under the name in the admin layout picker.
     * Return the empty string to omit.
     */
    public function getDescription(): string;

    /**
     * Group label used to categorize the layout in the picker (e.g.
     * "Prayers", "Content"). Layouts sharing a category render under
     * the same chip. Default `"Other"`.
     */
    public function getCategory(): string;

    /**
     * Public-asset path to a small preview image shown in the picker
     * card (e.g. `"/static/layout-previews/audio_prayer_section.png"`).
     * Return the empty string to fall back to a name-only tile.
     */
    public function getPreviewImage(): string;
}
