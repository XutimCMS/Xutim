<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Infra\Storage;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Infra\Storage\LocalStorageAdapter;
use Xutim\MediaBundle\Infra\Storage\StorageException;

final class LocalStorageAdapterTest extends TestCase
{
    private string $tmpDir;

    private LocalStorageAdapter $adapter;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/xutim_media_test_' . uniqid();
        mkdir($this->tmpDir, 0755, true);

        $this->adapter = new LocalStorageAdapter(
            publicDir: $this->tmpDir,
            mediaPath: 'media',
            urlPrefix: '/media',
        );
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        /** @var array<string> $items */
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function testWriteAndRead(): void
    {
        $this->adapter->write('test/file.txt', 'hello world');

        $content = $this->adapter->read('test/file.txt');

        $this->assertSame('hello world', $content);
    }

    public function testWriteCreatesDirectories(): void
    {
        $this->adapter->write('deep/nested/dir/file.txt', 'content');

        $this->assertTrue(is_file($this->tmpDir . '/media/deep/nested/dir/file.txt'));
    }

    public function testReadThrowsForMissingFile(): void
    {
        $this->expectException(StorageException::class);

        $this->adapter->read('nonexistent.txt');
    }

    public function testDelete(): void
    {
        $this->adapter->write('to-delete.txt', 'content');
        $this->assertTrue($this->adapter->exists('to-delete.txt'));

        $this->adapter->delete('to-delete.txt');

        $this->assertFalse($this->adapter->exists('to-delete.txt'));
    }

    public function testDeleteNonExistentFileIsNoop(): void
    {
        $this->adapter->delete('nonexistent.txt');

        $this->assertFalse($this->adapter->exists('nonexistent.txt'));
    }

    public function testExists(): void
    {
        $this->assertFalse($this->adapter->exists('check.txt'));

        $this->adapter->write('check.txt', 'content');

        $this->assertTrue($this->adapter->exists('check.txt'));
    }

    public function testUrl(): void
    {
        $url = $this->adapter->url('variants/thumb/320/webp/hash.webp');

        $this->assertSame('/media/variants/thumb/320/webp/hash.webp', $url);
    }

    public function testUrlTrimsSlashes(): void
    {
        $adapter = new LocalStorageAdapter(
            publicDir: $this->tmpDir,
            mediaPath: 'media',
            urlPrefix: '/media/',
        );

        $url = $adapter->url('/test/file.txt');

        $this->assertSame('/media/test/file.txt', $url);
    }

    public function testAbsolutePath(): void
    {
        $path = $this->adapter->absolutePath('test/file.txt');

        $this->assertSame($this->tmpDir . '/media/test/file.txt', $path);
    }
}
