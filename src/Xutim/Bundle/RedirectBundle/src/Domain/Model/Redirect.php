<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Uid\Uuid;

#[MappedSuperclass()]
abstract class Redirect implements RedirectInterface
{
    #[Id]
    #[Column(type: 'uuid')]
    protected Uuid $id;

    #[Column(type: Types::STRING)]
    protected string $source;

    #[Column(type: Types::STRING)]
    protected string $target;

    #[Column(type: Types::BOOLEAN)]
    protected bool $permanent;

    public function __construct(
        string $source,
        string $target,
        bool $permanent = false
    ) {
        $this->id = Uuid::v4();
        $this->change($source, $target, $permanent);
    }

    public function change(
        string $source,
        string $target,
        bool $permanent = false,
    ): void {
        $this->source = $source;
        $this->target = $target;
        $this->permanent = $permanent;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function isPermanent(): bool
    {
        return $this->permanent;
    }
}
