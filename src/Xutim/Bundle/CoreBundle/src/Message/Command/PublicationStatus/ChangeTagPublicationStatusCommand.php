<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Message\Command\PublicationStatus;

use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Entity\PublicationStatus;

final readonly class ChangeTagPublicationStatusCommand
{
    /**
     * @param Uuid $objectId
     */
    public function __construct(
        public Uuid $objectId,
        public PublicationStatus $status,
        public string $userIdentifier
    ) {
    }
}
