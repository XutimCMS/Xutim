<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Button
{
    public string $variant = 'primary';
    public string $tag = 'button';
    public bool $disabled = false;

    public function getVariantClasses(): string
    {
        return match ($this->variant) {
            'primary' => 'inline-flex items-center justify-center gap-1.5 rounded-md bg-accent px-4 py-2 text-[13px] font-medium text-white hover:bg-accent-hover dark:text-black transition-colors',
            'secondary', 'default' => 'inline-flex items-center justify-center gap-1.5 rounded-md border border-border px-2.5 py-1 text-[13px] text-content-secondary hover:bg-surface-raised transition-colors',
            'ghost' => 'inline-flex items-center justify-center gap-1.5 rounded-md px-2.5 py-1 text-[13px] text-content-secondary hover:bg-surface-raised hover:text-content transition-colors',
            'link' => 'inline-flex items-center gap-1.5 text-[13px] text-accent hover:underline transition-colors',
            'danger' => 'inline-flex items-center justify-center gap-1.5 rounded-md border border-red-200 px-2.5 py-1 text-[13px] text-red-600 hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10 transition-colors',
            'warning' => 'inline-flex items-center justify-center gap-1.5 rounded-md border border-amber-200 px-2.5 py-1 text-[13px] text-amber-600 hover:bg-amber-50 dark:border-amber-500/30 dark:text-amber-400 dark:hover:bg-amber-500/10 transition-colors',
            'success' => 'inline-flex items-center justify-center gap-1.5 rounded-md bg-green-600 px-4 py-2 text-[13px] font-medium text-white hover:bg-green-700 transition-colors',
            'nav' => 'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[13px] font-medium text-content-secondary hover:bg-surface-raised hover:text-content transition-colors',
            'tab' => 'px-3 py-2 text-[13px] font-medium text-content-secondary hover:text-content transition-colors',
            'dropdown' => 'flex w-full items-center gap-2 px-3 py-1.5 text-[13px] hover:bg-surface-raised transition-colors',
            'dashed' => 'inline-flex items-center justify-center gap-1 rounded-md border border-dashed border-border-strong px-1.5 py-0.5 text-[11px] font-medium text-content-tertiary hover:border-accent hover:text-accent transition-colors',
            'nav-outline' => 'inline-flex items-center justify-center gap-1.5 rounded-md border border-accent px-2.5 py-1 text-[13px] text-accent hover:bg-accent-subtle transition-colors',
            'card-link' => 'block rounded-xl border border-border p-4 hover:border-border-strong transition-colors',
            default => 'inline-flex items-center justify-center gap-1.5 rounded-md border border-border px-2.5 py-1 text-[13px] text-content-secondary hover:bg-surface-raised transition-colors',
        };
    }
}
