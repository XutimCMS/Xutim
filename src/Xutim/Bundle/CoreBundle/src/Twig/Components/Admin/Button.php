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
            'link' => 'btn-link',
            'primary' => 'btn btn-primary',
            'success' => 'btn btn-success',
            'danger' => 'btn btn-danger',
            'default' => 'btn btn-default',
            'secondary' => 'btn btn-secondary',
            'dropdown' => 'dropdown-item',
            'tab' => 'nav-link',
            'nav' => 'nav-link',
            'nav-outline' => 'btn-outline-primary btn',
            'card-link' => 'card card-link',
            default => throw new \LogicException(sprintf('Unknown button type "%s".', $this->variant))
        };
    }
}
