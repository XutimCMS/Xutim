<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

/**
 * Marker: this option's value is edited inline in the iframe preview
 * rather than through the admin modal form. Layouts whose fields are
 * ALL inline-editable hide the modal's Edit button entirely — useful
 * for layouts where nothing needs a dropdown / picker.
 *
 * Core types implementing this: TextBlockItemOption,
 * TextareaBlockItemOption, RichTextBlockItemOption. Downstream projects
 * can implement it on custom options (e.g. iframe-managed collections).
 */
interface InlineEditableOption extends BlockItemOption
{
}
