<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Twig\Runtime\XutimSectionDiffExtensionRuntime;
use Xutim\CoreBundle\Twig\Runtime\XutimSectionExtensionRuntime;

class XutimSectionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'render_xutim_section',
                [XutimSectionExtensionRuntime::class, 'render'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'fetch_xutim_sections',
                [XutimSectionExtensionRuntime::class, 'fetchSections']
            ),
            new TwigFunction(
                'xutim_editable',
                [XutimSectionExtensionRuntime::class, 'editable'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_is_inline_editable',
                [XutimSectionExtensionRuntime::class, 'isInlineEditable']
            ),
            new TwigFunction(
                'xutim_rich_text_html',
                [XutimSectionExtensionRuntime::class, 'richTextHtml'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_referenced_open',
                [XutimSectionExtensionRuntime::class, 'referencedOpen'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_referenced_close',
                [XutimSectionExtensionRuntime::class, 'referencedClose'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_static_open',
                [XutimSectionExtensionRuntime::class, 'staticOpen'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_static_close',
                [XutimSectionExtensionRuntime::class, 'staticClose'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_admin_edit_url',
                [XutimSectionExtensionRuntime::class, 'adminEditUrl']
            ),
            new TwigFunction(
                'xutim_section_name',
                [XutimSectionDiffExtensionRuntime::class, 'sectionName']
            ),
            new TwigFunction(
                'xutim_section_diff_field',
                [XutimSectionDiffExtensionRuntime::class, 'fieldDisplay']
            ),
        ];
    }
}
