<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block\Option;

use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

/**
 * Inline-editable rich text with a tiny mark set (bold + italic for now).
 * Stored as a TipTap-shaped array of text spans — each span has `text`
 * and an optional `marks` list — so we never parse or sanitize HTML.
 *
 *   [
 *     {"type": "text", "text": "Hello "},
 *     {"type": "text", "text": "world", "marks": [{"type": "bold"}]}
 *   ]
 *
 * Edited inline in the admin iframe via a contenteditable + minimal
 * toolbar; on the public side, rendered through `xutim_rich_text_html`
 * which emits `<b>` / `<i>` tags from the marks.
 */
readonly class RichTextBlockItemOption implements BlockItemOption, InlineEditableOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        return true;
    }

    public function getName(): string
    {
        return 'Rich text (bold, italic)';
    }

    public function isTranslatable(): bool
    {
        return true;
    }

    public function getDescription(): ?string
    {
        return 'Inline-editable text with bold/italic formatting, stored as structured JSON.';
    }
}
