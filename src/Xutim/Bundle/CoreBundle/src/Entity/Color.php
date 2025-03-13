<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class Color
{
    public const DEFAULT_VALUE_HEX = 'ef7d01';
    public const DEFAULT_VALUE_NAME = 'orange';

    #[Column(type: Types::STRING, length: 6, nullable: true)]
    private ?string $hexValue;

    public const array COLORS_BY_VALUE = [
        '6faa33' => 'green',
        'ef7d01' => 'orange',
        'c1292e' => 'red',
        '053c5e' => 'blue'
    ];

    public const array COLORS = [
        'green' => '6faa33',
        'orange' => 'ef7d01',
        'red' => 'c1292e',
        'blue' => '053c5e'
    ];

    public function __construct(?string $hexValue)
    {
        $this->hexValue = $hexValue;
    }

    /**
     * @phpstan-assert-if-true string $this->hexValue
     */
    public function isSet(): bool
    {
        return $this->hexValue !== null;
    }

    public function getHTMLHex(): string
    {
        if ($this->isSet() === false) {
            return '#666666';
        }

        return sprintf('#%s', $this->hexValue);
    }

    public function getHex(): ?string
    {
        return $this->hexValue;
    }

    public function getValueOrDefaultHex(): string
    {
        if ($this->hexValue === null) {
            return self::DEFAULT_VALUE_HEX;
        }

        return $this->hexValue;
    }

    public function getName(): ?string
    {
        if ($this->isSet() === false) {
            return null;
        }
        if (array_key_exists($this->hexValue, self::COLORS_BY_VALUE) === false) {
            return null;
        }

        return self::COLORS_BY_VALUE[$this->hexValue];
    }
}
