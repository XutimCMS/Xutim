<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Section;

/**
 * Marker for section definitions whose admin preview renders at the editor
 * column's real width. Other sections are laid out on a 900px desktop canvas
 * and scaled down, which shrinks small inline sections (a button, a badge)
 * below the size of the surrounding editor text.
 */
interface UnscaledPreview
{
}
