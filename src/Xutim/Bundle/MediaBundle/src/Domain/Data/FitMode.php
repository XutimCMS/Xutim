<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Domain\Data;

enum FitMode: string
{
    // Crop image to fill exact dimensions, focal point aware
    case Cover = 'cover';

    // Scale to fit within bounds, preserving aspect ratio
    case Contain = 'contain';

    // Scale to exact dimensions, may distort
    case Scale = 'scale';
}
