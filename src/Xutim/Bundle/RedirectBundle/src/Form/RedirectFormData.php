<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Form;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;
use Xutim\RedirectBundle\Domain\Model\RedirectInterface;

final readonly class RedirectFormData
{
    public function __construct(
        public ?string $source,
        public ?string $target,
        public ?bool $permanent,
        public ?Uuid $id = null
    ) {
    }

    public static function fromRedirect(RedirectInterface $redirect): self
    {
        return new self(
            $redirect->getSource(),
            $redirect->getTarget(),
            $redirect->isPermanent(),
            $redirect->getId()
        );
    }

    public function getSource(): string
    {
        Assert::string($this->source);

        return $this->source;
    }

    public function getTarget(): string
    {
        Assert::notNull($this->target);

        return $this->target;
    }

    public function isPermanent(): bool
    {
        Assert::boolean($this->permanent);

        return $this->permanent;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
