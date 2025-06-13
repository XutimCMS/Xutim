<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\SiteInterface;

class SiteFactory
{
    public function __construct(private readonly string $entityClass)
    {
        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('Site class "%s" does not exist.', $entityClass));
        }
    }

    public function create(): SiteInterface
    {
        /** @var SiteInterface $site */
        $site = new ($this->entityClass)();

        return $site;
    }
}
