<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout\Block;

use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;

final readonly class BlockOptionCollection
{
    /**
     * @param array<class-string, BlockItemOption> $options
     */
    public function __construct(private array $options)
    {
    }

    /**
     * @param class-string $needleOption
     */
    public function hasOption(string $needleOption): bool
    {
        return array_key_exists($needleOption, $this->options);
    }
}
