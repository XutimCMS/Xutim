<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Util;

/**
 * @template T
 */
final readonly class LinkedListNode
{
    /**
     * @param T $value
     * @param ?LinkedListNode<T> $next
     */
    public function __construct(
        public mixed $value,
        public ?LinkedListNode $next,
        public int $index
    ) {
    }

    /**
     * @phpstan-assert-if-true null $this->next
     * @phpstan-assert-if-false LinkedListNode<T> $this->next
     */
    public function isLast(): bool
    {
        return $this->next === null;
    }
}
