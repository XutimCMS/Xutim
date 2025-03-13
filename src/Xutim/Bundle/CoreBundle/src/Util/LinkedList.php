<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Util;

/**
 * @template T
 */
class LinkedList
{
    /** @var array<LinkedListNode<T>>  */
    private array $nodes;

    /** @var ?LinkedListNode<T>  */
    private ?LinkedListNode $first;

    /**
     * @param array<T> $values
     */
    public function __construct(array $values)
    {
        $this->nodes = [];
        $this->first = null;

        // For keys that are not sequential.
        $keys = array_reverse(array_keys($values));
        $prev = null;
        foreach ($keys as $index => $key) {
            $node = new LinkedListNode($values[$key], $prev, $key);
            $prev = $node;
            array_unshift($this->nodes, $node);
            $this->first = $node;
        }
    }

    public function isEmpty(): bool
    {
        return count($this->nodes) === 0;
    }

    /**
     * @return LinkedListNode<T>
     */
    public function getByIndex(int $index): LinkedListNode
    {
        return $this->nodes[$index];
    }

    /**
     * @return ?LinkedListNode<T>
     */
    public function getFirst(): ?LinkedListNode
    {
        return $this->first;
    }
}
