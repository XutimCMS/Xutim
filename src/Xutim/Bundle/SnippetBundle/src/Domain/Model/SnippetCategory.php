<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Domain\Model;

enum SnippetCategory: string
{
    case Ui = 'ui';
    case Route = 'route';
    case Anchor = 'anchor';

    public static function isValidCategory(string $value): bool
    {
        return in_array($value, array_column(SnippetCategory::cases(), 'value'), true);
    }
}
