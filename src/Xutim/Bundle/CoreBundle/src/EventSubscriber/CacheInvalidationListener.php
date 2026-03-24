<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Xutim\CoreBundle\Cache\CacheTagInvalidator;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Entity\MenuItem;
use Xutim\CoreBundle\Entity\Site;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetTranslationInterface;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
final class CacheInvalidationListener
{
    public function __construct(
        private readonly CacheTagInvalidator $invalidator
    ) {
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->invalidate($args->getObject());
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->invalidate($args->getObject());
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $args
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->invalidate($args->getObject());
    }

    private function invalidate(object $entity): void
    {
        $tags = $this->resolveTags($entity);

        if ($tags !== []) {
            $this->invalidator->invalidateTags($tags);
        }
    }

    /**
     * @return list<string>
     */
    private function resolveTags(object $entity): array
    {
        return match (true) {
            $entity instanceof ContentTranslationInterface => $this->resolveContentTranslationTags($entity),
            $entity instanceof ArticleInterface => $this->resolveArticleTags($entity),
            $entity instanceof PageInterface => ['page.' . $entity->getId(), 'menu', 'page_tree'],
            $entity instanceof TagInterface => ['tag.' . $entity->getId()],
            $entity instanceof MediaInterface => ['media.' . $entity->id()],
            $entity instanceof SnippetTranslationInterface => ['snippet.' . $entity->getSnippet()->getCode()],
            $entity instanceof BlockItemInterface => ['block.' . $entity->getBlock()->getCode()],
            $entity instanceof BlockInterface => ['block.' . $entity->getCode()],
            $entity instanceof MenuItem => ['menu'],
            $entity instanceof Site => ['site'],
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    private function resolveContentTranslationTags(ContentTranslationInterface $trans): array
    {
        $tags = [];

        if ($trans->hasArticle()) {
            $article = $trans->getArticle();
            $tags[] = 'article.' . $article->getId();

            foreach ($article->getTags() as $tag) {
                $tags[] = 'tag.' . $tag->getId();
            }
        }

        if ($trans->hasPage()) {
            $tags[] = 'page.' . $trans->getPage()->getId();
            $tags[] = 'menu';
            $tags[] = 'page_tree';
        }

        return $tags;
    }

    /**
     * @return list<string>
     */
    private function resolveArticleTags(ArticleInterface $article): array
    {
        $tags = ['article.' . $article->getId()];

        foreach ($article->getTags() as $tag) {
            $tags[] = 'tag.' . $tag->getId();
        }

        return $tags;
    }
}
