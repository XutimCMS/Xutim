<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Admin\Entity;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Page;

class ContentTranslationTest extends TestCase
{
    public function testCanInstantiateContentTranslation(): void
    {
        $page = new Page(null, 'fffeff', ['fr'], 'pretitle', 'Page Name', 'subtitle', 'page-slug', [], 'en', 'page description', null);
        $translation = new ContentTranslation(
            'pretitle',
            'Title',
            'subtitle',
            'slug',
            [],
            'en',
            'Description',
            $page,
            null
        );

        $this->assertInstanceOf(ContentTranslation::class, $translation);
        $this->assertInstanceOf(Page::class, $translation->getPage());
        $this->assertSame('Title', $translation->getTitle());
        $this->assertSame('pretitle', $translation->getPreTitle());
        $this->assertSame('subtitle', $translation->getSubTitle());
        $this->assertSame('slug', $translation->getSlug());
        $this->assertSame([], $translation->getContent());
        $this->assertSame('en', $translation->getLocale());
        $this->assertSame('Description', $translation->getDescription());
    }

    public function testCanChangeContentTranslation(): void
    {
        $page = new Page(null, 'fffeff', ['fr'], 'pretitle', 'Page Name', 'subtitle', 'page-slug', [], 'en', 'page description', null);
        $translation = new ContentTranslation(
            'pretitle',
            'Title',
            'subtitle',
            'slug',
            [],
            'en',
            'Description',
            $page,
            null
        );

        $translation->change(
            'pretitle2',
            'New Title',
            'subtitle2',
            'new-slug',
            ['time' => 1726446064919, 'blocks' => ['id' => 'sldfkj', 'type' => 'paragraph', 'data' => ['text' => 'A test paragrapgh']], 'version' => '2.30.5'],
            'fr',
            'New Description'
        );

        $this->assertSame('pretitle2', $translation->getPreTitle());
        $this->assertSame('New Title', $translation->getTitle());
        $this->assertSame('subtitle2', $translation->getSubTitle());
        $this->assertSame('new-slug', $translation->getSlug());
        $this->assertSame(['time' => 1726446064919, 'blocks' => ['id' => 'sldfkj', 'type' => 'paragraph', 'data' => ['text' => 'A test paragrapgh']], 'version' => '2.30.5'], $translation->getContent());
        $this->assertSame('fr', $translation->getLocale());
        $this->assertSame('New Description', $translation->getDescription());
    }
}
