<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Twig\Components\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Infra\Storage\StorageAdapterInterface;

#[AsTwigComponent]
final class FocalPoint
{
    private const int GRID_SIZE = 5;
    private const array STEPS = [0.1, 0.3, 0.5, 0.7, 0.9];

    public MediaInterface $media;
    public string $saveUrl;

    public function __construct(
        private readonly StorageAdapterInterface $storage,
    ) {
    }

    public function getActiveZone(): int
    {
        $focalX = $this->media->focalX() ?? 0.5;
        $focalY = $this->media->focalY() ?? 0.5;

        $bestZone = 12;
        $bestDistance = PHP_FLOAT_MAX;

        $totalZones = self::GRID_SIZE * self::GRID_SIZE;
        for ($i = 0; $i < $totalZones; $i++) {
            $col = $i % self::GRID_SIZE;
            $row = intdiv($i, self::GRID_SIZE);
            $zx = self::STEPS[$col];
            $zy = self::STEPS[$row];

            $distance = ($focalX - $zx) ** 2 + ($focalY - $zy) ** 2;
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestZone = $i;
            }
        }

        return $bestZone;
    }

    public function getGridSize(): int
    {
        return self::GRID_SIZE;
    }

    public function getTotalZones(): int
    {
        return self::GRID_SIZE * self::GRID_SIZE;
    }

    public function getImageUrl(): string
    {
        return $this->storage->url($this->media->originalPath());
    }
}
