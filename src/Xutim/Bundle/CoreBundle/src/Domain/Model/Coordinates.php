<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

readonly class Coordinates
{
    public function __construct(public float $latitude, public float $longitude)
    {
    }
}
