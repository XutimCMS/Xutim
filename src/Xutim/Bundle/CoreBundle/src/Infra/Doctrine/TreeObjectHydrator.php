<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Doctrine;

use Doctrine\ORM\Internal\Hydration\ObjectHydrator;

/**
 * A copy of Gedmo TreeObjectHydrator that allows UUIDs as a primary keys.
 */
class TreeObjectHydrator extends ObjectHydrator
{
}
