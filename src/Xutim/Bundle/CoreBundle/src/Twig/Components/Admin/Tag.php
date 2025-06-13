<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xutim\CoreBundle\Entity\Color;

#[AsTwigComponent(name: 'Xutim:Admin:Tag')]
final class Tag
{
    public Color $color;
}
