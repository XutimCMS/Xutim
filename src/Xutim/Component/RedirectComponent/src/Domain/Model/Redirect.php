<?php

declare(strict_types=1);

namespace Xutim\RedirectComponent\Domain\Model;

use Symfony\Component\Uid\Uuid;

abstract class Redirect implements RedirectInterface
{
    protected Uuid $id;

    public function __construct(
        protected string $source,
        protected ?string $targetUrl = null,
        protected ?string $locale = null,
        protected bool $permanent = false
    ) {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTargetUrl(): ?string
    {
        return $this->targetUrl;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function isPermanent(): bool
    {
        return $this->permanent;
    }
}
