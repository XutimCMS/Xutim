<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Cache;

class SnippetUsageTracker
{
    /** @var list<list<string>> */
    private array $stack = [];

    public function push(): void
    {
        $this->stack[] = [];
    }

    /** @return list<string> */
    public function pop(): array
    {
        if ($this->stack === []) {
            return [];
        }

        return array_values(array_unique(array_pop($this->stack)));
    }

    public function track(string $code): void
    {
        if ($this->stack === []) {
            return;
        }

        $this->stack[count($this->stack) - 1][] = $code;
    }
}
