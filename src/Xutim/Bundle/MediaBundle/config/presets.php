<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Xutim\MediaBundle\Domain\Data\FitMode;
use Xutim\MediaBundle\Domain\Data\ImagePreset;

// Default admin presets - applications should define their own front-end presets
return [
    new ImagePreset(
        name: 'thumb_small',
        maxWidth: 227,
        maxHeight: 227,
        fitMode: FitMode::Cover,
        useFocalPoint: true,
        formats: ['avif', 'webp', 'jpg'],
        responsiveWidths: [227],
    ),
    new ImagePreset(
        name: 'thumb_large',
        maxWidth: 300,
        maxHeight: 300,
        fitMode: FitMode::Cover,
        useFocalPoint: true,
        formats: ['avif', 'webp', 'jpg'],
        responsiveWidths: [300],
    ),
];
