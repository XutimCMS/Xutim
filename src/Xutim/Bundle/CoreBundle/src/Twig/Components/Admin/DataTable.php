<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Components\Admin;

use Xutim\CoreBundle\Dto\Admin\FilterDto;

class DataTable
{
    public string $frameId;
    public string $tableBodyId;
    public iterable $pager;
    public FilterDto $filter;
}
