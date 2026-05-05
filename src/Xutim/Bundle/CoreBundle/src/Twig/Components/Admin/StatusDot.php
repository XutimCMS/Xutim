<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xutim\CoreBundle\Entity\PublicationStatus;

#[AsTwigComponent(name: 'Xutim:Admin:StatusDot')]
final class StatusDot
{
    public ?PublicationStatus $status = null;
    public bool $referenceHasChanged = false;
}
