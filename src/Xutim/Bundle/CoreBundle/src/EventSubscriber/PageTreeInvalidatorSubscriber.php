<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Xutim\CoreBundle\Context\PageTreeContext;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
final class PageTreeInvalidatorSubscriber
{
    public function __construct(private readonly PageTreeContext $context)
    {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->invalidate($args->getObject());
    }
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->invalidate($args->getObject());
    }
    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->invalidate($args->getObject());
    }

    private function invalidate(object $entity): void
    {
        if ($entity instanceof PageInterface) {
            $this->context->resetAll();
            return;
        }

        if ($entity instanceof ContentTranslationInterface) {
            $this->context->resetForLocale($entity->getLocale());
        }
    }
}
