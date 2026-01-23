<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Tests\Unit\Entity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xutim\EditorBundle\Entity\DraftStatus;

final class DraftStatusTest extends TestCase
{
    public function testLiveStatus(): void
    {
        $status = DraftStatus::LIVE;

        $this->assertSame('live', $status->value);
        $this->assertTrue($status->isLive());
        $this->assertFalse($status->isEditing());
        $this->assertFalse($status->isStale());
        $this->assertFalse($status->isDiscarded());
    }

    public function testEditingStatus(): void
    {
        $status = DraftStatus::EDITING;

        $this->assertSame('editing', $status->value);
        $this->assertFalse($status->isLive());
        $this->assertTrue($status->isEditing());
        $this->assertFalse($status->isStale());
        $this->assertFalse($status->isDiscarded());
    }

    public function testStaleStatus(): void
    {
        $status = DraftStatus::STALE;

        $this->assertSame('stale', $status->value);
        $this->assertFalse($status->isLive());
        $this->assertFalse($status->isEditing());
        $this->assertTrue($status->isStale());
        $this->assertFalse($status->isDiscarded());
    }

    public function testDiscardedStatus(): void
    {
        $status = DraftStatus::DISCARDED;

        $this->assertSame('discarded', $status->value);
        $this->assertFalse($status->isLive());
        $this->assertFalse($status->isEditing());
        $this->assertFalse($status->isStale());
        $this->assertTrue($status->isDiscarded());
    }

    #[DataProvider('statusValueProvider')]
    public function testFromValue(string $value, DraftStatus $expected): void
    {
        $this->assertSame($expected, DraftStatus::from($value));
    }

    /**
     * @return iterable<string, array{string, DraftStatus}>
     */
    public static function statusValueProvider(): iterable
    {
        yield 'live' => ['live', DraftStatus::LIVE];
        yield 'editing' => ['editing', DraftStatus::EDITING];
        yield 'stale' => ['stale', DraftStatus::STALE];
        yield 'discarded' => ['discarded', DraftStatus::DISCARDED];
    }
}
