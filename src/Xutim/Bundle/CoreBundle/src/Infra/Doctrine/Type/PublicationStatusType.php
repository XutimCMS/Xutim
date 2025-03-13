<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Doctrine\Type;

use Xutim\CoreBundle\Entity\PublicationStatus;

class PublicationStatusType extends AbstractEnumType
{
    public const NAME = 'publication_status';

    public function getName(): string
    {
        return self::NAME;
    }

    public static function getEnumsClass(): string
    {
        return PublicationStatus::class;
    }
}
