<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Xutim\CoreBundle\Twig\Runtime\XutimLayoutExtensionRuntime;

class XutimLayoutExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'render_xutim_layout',
                [XutimLayoutExtensionRuntime::class, 'render'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'fetch_xutim_layouts',
                [XutimLayoutExtensionRuntime::class, 'fetchLayouts']
            ),
            new TwigFunction(
                'xutim_editable',
                [XutimLayoutExtensionRuntime::class, 'editable'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_is_inline_editable',
                [XutimLayoutExtensionRuntime::class, 'isInlineEditable']
            ),
            new TwigFunction(
                'xutim_rich_text_html',
                [XutimLayoutExtensionRuntime::class, 'richTextHtml'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_referenced_open',
                [XutimLayoutExtensionRuntime::class, 'referencedOpen'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_referenced_close',
                [XutimLayoutExtensionRuntime::class, 'referencedClose'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_static_open',
                [XutimLayoutExtensionRuntime::class, 'staticOpen'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_static_close',
                [XutimLayoutExtensionRuntime::class, 'staticClose'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),
            new TwigFunction(
                'xutim_admin_edit_url',
                [XutimLayoutExtensionRuntime::class, 'adminEditUrl']
            ),
        ];
    }
}
