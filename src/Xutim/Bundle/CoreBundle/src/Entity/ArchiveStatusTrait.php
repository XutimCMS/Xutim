<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\ORM\Mapping\Column;

trait ArchiveStatusTrait
{
    #[column(type: 'boolean', nullable: false)]
    private bool $archived;

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function archive(): void
    {
        $this->archived = true;
    }
}
