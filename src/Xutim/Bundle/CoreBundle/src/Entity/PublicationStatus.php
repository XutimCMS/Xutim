<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

enum PublicationStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Scheduled = 'scheduled';
    case Archived = 'archived';
    case Evaluation = 'evaluation';

    public function getColorClass(): string
    {
        return match ($this) {
            PublicationStatus::Draft => 'primary',
            PublicationStatus::Published => 'success',
            PublicationStatus::Archived => 'secondary',
            PublicationStatus::Evaluation => 'warning',
            PublicationStatus::Scheduled => 'purple'
        };
    }

    public function canPublish(): bool
    {
        return !in_array($this->value, ['archived', 'published'], true);
    }

    public function canSubmitForEvaluation(): bool
    {
        return $this->value !== 'evaluation';
    }

    public function canBeArchived(): bool
    {
        return $this->value !== 'archived';
    }

    public function canBeDrafted(): bool
    {
        return $this->value !== 'draft';
    }

    public function canBeScheduled(): bool
    {
        return $this->value !== 'scheduled';
    }

    public function isPublished(): bool
    {
        return $this->value === 'published';
    }

    public function isScheduled(): bool
    {
        return $this->value === 'scheduled';
    }
}
