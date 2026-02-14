<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Service;

use Xutim\MediaBundle\Domain\Data\FitMode;
use Xutim\MediaBundle\Domain\Data\ImagePreset;

final class PresetRegistry
{
    /** @var array<string, ImagePreset> */
    private array $presets = [];

    /**
     * @param array<string, array{max_width: int, max_height: int, fit_mode: string, quality: array<string, int>, use_focal_point: bool, formats: list<string>, responsive_widths: list<int>}> $presetsConfig
     */
    public function __construct(array $presetsConfig = [])
    {
        foreach ($presetsConfig as $name => $config) {
            $this->presets[$name] = new ImagePreset(
                name: $name,
                maxWidth: $config['max_width'],
                maxHeight: $config['max_height'],
                fitMode: FitMode::from($config['fit_mode']),
                quality: $config['quality'],
                useFocalPoint: $config['use_focal_point'],
                formats: $config['formats'],
                responsiveWidths: $config['responsive_widths'],
            );
        }
    }

    public function add(ImagePreset $preset): void
    {
        $this->presets[$preset->name] = $preset;
    }

    public function get(string $name): ?ImagePreset
    {
        return $this->presets[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->presets[$name]);
    }

    /**
     * @return array<string, ImagePreset>
     */
    public function all(): array
    {
        return $this->presets;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->presets);
    }
}
