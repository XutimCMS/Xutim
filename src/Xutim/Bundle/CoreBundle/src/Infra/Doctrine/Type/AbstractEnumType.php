<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Infra\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class AbstractEnumType extends Type
{
    abstract public static function getEnumsClass(): string;

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'VARCHAR(255)';
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (false === enum_exists($this::getEnumsClass(), true)) {
            throw new \LogicException('This class should be an enum');
        }
        // 🔥 https://www.php.net/manual/en/backedenum.tryfrom.php
        return $this::getEnumsClass()::tryFrom($value);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
