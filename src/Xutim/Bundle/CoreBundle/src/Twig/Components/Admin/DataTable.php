<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Pagerfanta\Pagerfanta;
use Xutim\CoreBundle\Dto\Admin\FilterDto;

/**
 * @template T
 */
class DataTable
{
    public string $frameId;
    public string $tableBodyId;
    /**
     * @var Pagerfanta<T> $pager
     */
    public iterable $pager;
    public FilterDto $filter;
    
    public bool $responsive = true;

    public bool $showChips = true;

    public bool $borderless = false;

    public string $filterFormId = '';

    /**
     * @var list<array<string,mixed>>
     */
    public array $columns = [];

    /**
     * @var list<array{key: string, label: string, type: string, chipLabel: string, options?: list<array{label?: string, value?: string, separator?: bool, checked?: bool}>, minWidth?: string}>
     */
    public array $filters = [];
}
