<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

final class Dropdown
{
    public string $placement = 'bottom-start';
    public string $menuClass = 'min-w-[180px] rounded-xl border border-border bg-surface shadow-lg py-1';
    public string $controller = 'dropdown';
}
