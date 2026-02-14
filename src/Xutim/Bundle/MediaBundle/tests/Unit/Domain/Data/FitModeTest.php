<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Tests\Unit\Domain\Data;

use PHPUnit\Framework\TestCase;
use Xutim\MediaBundle\Domain\Data\FitMode;

final class FitModeTest extends TestCase
{
    public function testCoverValue(): void
    {
        $this->assertSame('cover', FitMode::Cover->value);
    }

    public function testContainValue(): void
    {
        $this->assertSame('contain', FitMode::Contain->value);
    }

    public function testScaleValue(): void
    {
        $this->assertSame('scale', FitMode::Scale->value);
    }

    public function testFromString(): void
    {
        $this->assertSame(FitMode::Cover, FitMode::from('cover'));
        $this->assertSame(FitMode::Contain, FitMode::from('contain'));
        $this->assertSame(FitMode::Scale, FitMode::from('scale'));
    }
}
