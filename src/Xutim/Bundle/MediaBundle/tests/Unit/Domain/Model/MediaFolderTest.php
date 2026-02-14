<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Model\MediaFolder;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;

final class MediaFolderTest extends TestCase
{
    public function testConstructor(): void
    {
        $folder = new MediaFolder(
            code: 'articles',
            name: 'Articles',
            basePath: '/media/articles/',
        );

        $this->assertNotNull($folder->id());
        $this->assertSame('articles', $folder->code());
        $this->assertSame('Articles', $folder->name());
        $this->assertSame('media/articles', $folder->basePath());
        $this->assertTrue($folder->isActive());
        $this->assertNull($folder->parent());
        $this->assertCount(0, $folder->children());
        $this->assertCount(0, $folder->media());
        $this->assertNotNull($folder->createdAt());
        $this->assertNotNull($folder->updatedAt());
    }

    public function testConstructorTrimsBasePath(): void
    {
        $folder = new MediaFolder(
            code: 'test',
            name: 'Test',
            basePath: '//media/test//',
        );

        $this->assertSame('media/test', $folder->basePath());
    }

    public function testConstructorWithParent(): void
    {
        $parent = new MediaFolder(code: 'root', name: 'Root', basePath: 'media');
        $child = new MediaFolder(
            code: 'sub',
            name: 'Sub',
            basePath: 'media/sub',
            parent: $parent,
        );

        $this->assertSame($parent, $child->parent());
    }

    public function testChange(): void
    {
        $folder = new MediaFolder(code: 'test', name: 'Test', basePath: 'media');
        $parent = $this->createStub(MediaFolderInterface::class);

        $folder->change(
            name: 'Updated',
            basePath: '/new/path/',
            active: false,
            parent: $parent,
        );

        $this->assertSame('Updated', $folder->name());
        $this->assertSame('new/path', $folder->basePath());
        $this->assertFalse($folder->isActive());
        $this->assertSame($parent, $folder->parent());
    }

    public function testChangeSkipsNullValues(): void
    {
        $folder = new MediaFolder(
            code: 'test',
            name: 'Original',
            basePath: 'media/orig',
        );

        $folder->change();

        $this->assertSame('Original', $folder->name());
        $this->assertSame('media/orig', $folder->basePath());
        $this->assertTrue($folder->isActive());
    }

    public function testChangeName(): void
    {
        $folder = new MediaFolder(code: 'test', name: 'Old', basePath: 'media');

        $folder->changeName('New Name');

        $this->assertSame('New Name', $folder->name());
    }

    public function testFolderPath(): void
    {
        $root = new MediaFolder(code: 'root', name: 'Root', basePath: 'media');
        $child = new MediaFolder(code: 'child', name: 'Child', basePath: 'media/child', parent: $root);
        $grandchild = new MediaFolder(code: 'gc', name: 'Grandchild', basePath: 'media/child/gc', parent: $child);

        $path = $grandchild->folderPath();

        $this->assertCount(3, $path);
        $this->assertSame($root, $path[0]);
        $this->assertSame($child, $path[1]);
        $this->assertSame($grandchild, $path[2]);
    }

    public function testFolderPathSingleNode(): void
    {
        $folder = new MediaFolder(code: 'root', name: 'Root', basePath: 'media');

        $path = $folder->folderPath();

        $this->assertCount(1, $path);
        $this->assertSame($folder, $path[0]);
    }
}
