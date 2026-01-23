<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Entity;

enum DraftStatus: string
{
    case LIVE = 'live';
    case EDITING = 'editing';
    case STALE = 'stale';
    case DISCARDED = 'discarded';

    public function isLive(): bool
    {
        return $this === self::LIVE;
    }

    public function isEditing(): bool
    {
        return $this === self::EDITING;
    }

    public function isStale(): bool
    {
        return $this === self::STALE;
    }

    public function isDiscarded(): bool
    {
        return $this === self::DISCARDED;
    }
}
