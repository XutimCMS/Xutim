<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ArrayExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('findIndex', [$this, 'findIndex']),
        ];
    }

    /**
     * Find a position of the item in an array. The num parameter defines
     * how many found items it should skip.
     * @template TKey of array-key
     * @template T
     * @param array<TKey, T>   $array
     * @param callable(T):bool $callback
     */
    public function findIndex(array $array, callable $callback, int $hitSkip = 0): ?int
    {
        $skipped = 0;
        foreach ($array as $index => $item) {
            if ($callback($item)) {
                if ($hitSkip === $skipped) {
                    return $index;
                }
                $skipped++;
            }
        }

        return null;
    }
}
