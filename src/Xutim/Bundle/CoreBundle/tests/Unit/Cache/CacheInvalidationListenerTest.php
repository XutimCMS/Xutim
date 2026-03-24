<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Cache;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Cache\CacheTagInvalidator;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\BlockInterface;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Entity\MenuItem;
use Xutim\CoreBundle\Entity\Site;
use Xutim\CoreBundle\EventSubscriber\CacheInvalidationListener;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetInterface;
use Xutim\SnippetBundle\Domain\Model\SnippetTranslationInterface;

final class CacheInvalidationListenerTest extends TestCase
{
    private CacheTagInvalidator $invalidator;
    private CacheInvalidationListener $listener;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->invalidator = $this->createMock(CacheTagInvalidator::class);
        $this->listener = new CacheInvalidationListener($this->invalidator);
        $this->em = $this->createStub(EntityManagerInterface::class);
    }

    public function test_article_invalidates_article_and_tag_tags(): void
    {
        $tag1 = $this->stubTag('t1');
        $tag2 = $this->stubTag('t2');

        $article = $this->createStub(ArticleInterface::class);
        $article->method('getId')->willReturn($this->id('a1'));
        $article->method('getTags')->willReturn(new ArrayCollection([$tag1, $tag2]));

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['article.' . $this->id('a1'), 'tag.' . $this->id('t1'), 'tag.' . $this->id('t2')]);

        $this->listener->postUpdate($this->args($article));
    }

    public function test_article_without_tags(): void
    {
        $article = $this->createStub(ArticleInterface::class);
        $article->method('getId')->willReturn($this->id('a1'));
        $article->method('getTags')->willReturn(new ArrayCollection());

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['article.' . $this->id('a1')]);

        $this->listener->postUpdate($this->args($article));
    }

    public function test_page_invalidates_page_menu_pagetree(): void
    {
        $page = $this->createStub(PageInterface::class);
        $page->method('getId')->willReturn($this->id('p1'));

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['page.' . $this->id('p1'), 'menu', 'page_tree']);

        $this->listener->postUpdate($this->args($page));
    }

    public function test_content_translation_for_article(): void
    {
        $tag1 = $this->stubTag('t1');

        $article = $this->createStub(ArticleInterface::class);
        $article->method('getId')->willReturn($this->id('a1'));
        $article->method('getTags')->willReturn(new ArrayCollection([$tag1]));

        $trans = $this->createStub(ContentTranslationInterface::class);
        $trans->method('hasArticle')->willReturn(true);
        $trans->method('getArticle')->willReturn($article);
        $trans->method('hasPage')->willReturn(false);

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['article.' . $this->id('a1'), 'tag.' . $this->id('t1')]);

        $this->listener->postUpdate($this->args($trans));
    }

    public function test_content_translation_for_page(): void
    {
        $page = $this->createStub(PageInterface::class);
        $page->method('getId')->willReturn($this->id('p1'));

        $trans = $this->createStub(ContentTranslationInterface::class);
        $trans->method('hasArticle')->willReturn(false);
        $trans->method('hasPage')->willReturn(true);
        $trans->method('getPage')->willReturn($page);

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['page.' . $this->id('p1'), 'menu', 'page_tree']);

        $this->listener->postUpdate($this->args($trans));
    }

    public function test_tag_invalidates_tag_tag(): void
    {
        $tag = $this->createStub(TagInterface::class);
        $tag->method('getId')->willReturn($this->id('t1'));

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['tag.' . $this->id('t1')]);

        $this->listener->postUpdate($this->args($tag));
    }

    public function test_media_invalidates_media_tag(): void
    {
        $media = $this->createStub(MediaInterface::class);
        $media->method('id')->willReturn($this->id('m1'));

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['media.' . $this->id('m1')]);

        $this->listener->postUpdate($this->args($media));
    }

    public function test_snippet_translation_invalidates_snippet_tag(): void
    {
        $snippet = $this->createStub(SnippetInterface::class);
        $snippet->method('getCode')->willReturn('read-more');

        $snippetTrans = $this->createStub(SnippetTranslationInterface::class);
        $snippetTrans->method('getSnippet')->willReturn($snippet);

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['snippet.read-more']);

        $this->listener->postUpdate($this->args($snippetTrans));
    }

    public function test_block_item_invalidates_parent_block(): void
    {
        $block = $this->createStub(BlockInterface::class);
        $block->method('getCode')->willReturn('footer');

        $blockItem = $this->createStub(BlockItemInterface::class);
        $blockItem->method('getBlock')->willReturn($block);

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['block.footer']);

        $this->listener->postUpdate($this->args($blockItem));
    }

    public function test_block_invalidates_block_tag(): void
    {
        $block = $this->createStub(BlockInterface::class);
        $block->method('getCode')->willReturn('world-map');

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['block.world-map']);

        $this->listener->postUpdate($this->args($block));
    }

    public function test_menu_item_invalidates_menu(): void
    {
        $menuItem = $this->createStub(MenuItem::class);

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['menu']);

        $this->listener->postUpdate($this->args($menuItem));
    }

    public function test_site_invalidates_site(): void
    {
        $site = $this->createStub(Site::class);

        $this->invalidator->expects(self::once())
            ->method('invalidateTags')
            ->with(['site']);

        $this->listener->postUpdate($this->args($site));
    }

    public function test_unknown_entity_does_not_invalidate(): void
    {
        $this->invalidator->expects(self::never())->method('invalidateTags');

        $this->listener->postUpdate($this->args(new \stdClass()));
    }

    public function test_all_three_events_trigger_invalidation(): void
    {
        $tag = $this->createStub(TagInterface::class);
        $tag->method('getId')->willReturn($this->id('t1'));

        $this->invalidator->expects(self::exactly(3))->method('invalidateTags');

        $this->listener->postPersist($this->args($tag));
        $this->listener->postUpdate($this->args($tag));
        $this->listener->postRemove($this->args($tag));
    }

    private function id(string $alias): Uuid
    {
        $h = md5($alias);

        return Uuid::fromString(sprintf(
            '%s-%s-4%s-8%s-%s',
            substr($h, 0, 8),
            substr($h, 8, 4),
            substr($h, 12, 3),
            substr($h, 15, 3),
            substr($h, 18, 12)
        ));
    }

    private function stubTag(string $alias): TagInterface
    {
        $tag = $this->createStub(TagInterface::class);
        $tag->method('getId')->willReturn($this->id($alias));

        return $tag;
    }

    private function args(object $entity): LifecycleEventArgs
    {
        return new LifecycleEventArgs($entity, $this->em);
    }
}
