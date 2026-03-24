<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Tests\Unit\Cache;

use PHPUnit\Framework\TestCase;
use Xutim\CoreBundle\Cache\SnippetUsageTracker;

final class SnippetUsageTrackerTest extends TestCase
{
    private SnippetUsageTracker $tracker;

    protected function setUp(): void
    {
        $this->tracker = new SnippetUsageTracker();
    }

    public function test_track_without_push_is_ignored(): void
    {
        $this->tracker->track('read-more');

        self::assertSame([], $this->tracker->pop());
    }

    public function test_push_pop_returns_tracked_codes(): void
    {
        $this->tracker->push();
        $this->tracker->track('read-more');
        $this->tracker->track('download-button');

        self::assertSame(['read-more', 'download-button'], $this->tracker->pop());
    }

    public function test_pop_deduplicates(): void
    {
        $this->tracker->push();
        $this->tracker->track('read-more');
        $this->tracker->track('read-more');
        $this->tracker->track('download-button');
        $this->tracker->track('read-more');

        self::assertSame(['read-more', 'download-button'], $this->tracker->pop());
    }

    public function test_nested_push_pop_isolates_scopes(): void
    {
        $this->tracker->push();
        $this->tracker->track('outer-snippet');

        $this->tracker->push();
        $this->tracker->track('inner-snippet');

        self::assertSame(['inner-snippet'], $this->tracker->pop());
        self::assertSame(['outer-snippet'], $this->tracker->pop());
    }

    public function test_pop_on_empty_stack_returns_empty(): void
    {
        self::assertSame([], $this->tracker->pop());
    }

    public function test_deeply_nested_scopes(): void
    {
        $this->tracker->push();
        $this->tracker->track('level-1');

        $this->tracker->push();
        $this->tracker->track('level-2');

        $this->tracker->push();
        $this->tracker->track('level-3a');
        $this->tracker->track('level-3b');

        self::assertSame(['level-3a', 'level-3b'], $this->tracker->pop());
        self::assertSame(['level-2'], $this->tracker->pop());
        self::assertSame(['level-1'], $this->tracker->pop());
        self::assertSame([], $this->tracker->pop());
    }
}
