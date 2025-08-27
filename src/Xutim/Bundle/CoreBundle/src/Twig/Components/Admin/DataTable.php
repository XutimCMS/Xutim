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

    public string $filterFormId = '';

    /**
     * @var list<array<string,mixed>>
     */
    public array $columns = [];
}
