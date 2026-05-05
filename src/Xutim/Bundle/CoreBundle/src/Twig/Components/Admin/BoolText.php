<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Xutim:Admin:BoolText')]
final class BoolText
{
    public bool $value;
    public string $trueLabel = 'yes';
    public string $falseLabel = 'no';
    public string $domain = 'admin';
}
