<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;

return static function (MBConfig $mbConfig): void {
    $mbConfig->packageDirectories([
        __DIR__ . '/src/Xutim',
        __DIR__ . '/src/Xutim/Bundle',
        __DIR__ . '/src/Xutim/Component',
    ]);
    $mbConfig->defaultBranch('main');
};
