<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\Dto;

final class LayoutValuesDto
{
    /**
     * @param array<string, mixed> $values field name => persisted or form-native value
     */
    public function __construct(public array $values = [])
    {
    }
}
