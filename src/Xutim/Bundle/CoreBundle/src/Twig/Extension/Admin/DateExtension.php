<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension\Admin;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_until', [$this, 'timeUntil']),
            new TwigFilter('time_ago', [$this, 'timeAgo']),
        ];
    }

    public function timeUntil(\DateTimeInterface $date): string
    {
        $now = new \DateTimeImmutable();
        $interval = $now->diff($date);

        if ($interval->invert === 1) {
            return $this->timeAgo($date);
        }

        $parts = [];

        if ($interval->days > 0) {
            $parts[] = $interval->days . ' day' . ($interval->days > 1 ? 's' : '');
        }

        if ($interval->h > 0) {
            $parts[] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
        }

        if ($interval->i > 0 && count($parts) < 2) { // Show minutes only if no hours or less than 2 components.
            $parts[] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
        }

        return implode(', ', $parts);
    }

    public function timeAgo(\DateTimeInterface $dateTime): string
    {
        $now = new \DateTime();
        $interval = $now->diff($dateTime);

        if ($interval->invert === 0) {
            return $this->timeUntil($dateTime);
        }

        if ($interval->y > 0) {
            return $interval->y === 1 ? '1 year ago' : $interval->y . ' years ago';
        }
        if ($interval->m > 0) {
            return $interval->m === 1 ? '1 month ago' : $interval->m . ' months ago';
        }
        if ($interval->d >= 7) {
            $weeks = (int)floor($interval->d / 7);
            return $weeks === 1 ? '1 week ago' : $weeks . ' weeks ago';
        }
        if ($interval->d > 0) {
            return $interval->d === 1 ? '1 day ago' : $interval->d . ' days ago';
        }
        if ($interval->h > 0) {
            return $interval->h === 1 ? '1 hour ago' : $interval->h . ' hours ago';
        }
        if ($interval->i > 0) {
            return $interval->i === 1 ? '1 minute ago' : $interval->i . ' minutes ago';
        }

        return 'just now';
    }
}
