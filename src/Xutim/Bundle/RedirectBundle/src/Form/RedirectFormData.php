<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Form\Admin;

use Webmozart\Assert\Assert;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\RedirectBundle\Domain\Model\CmsRedirectInterface;

final readonly class RedirectFormData
{
    public function __construct(
        public ?string $source,
        public ?ContentTranslationInterface $targetContentTranslation,
        public ?string $locale,
        public ?bool $permanent,
    ) {
    }

    public static function fromRedirect(CmsRedirectInterface $redirect): self
    {
        return new self(
            $redirect->getSource(),
            $redirect->getTargetContentTranslation(),
            $redirect->getLocale(),
            $redirect->isPermanent()
        );
    }

    public function getSource(): string
    {
        Assert::string($this->source);

        return $this->source;
    }

    public function getTargetContentTranslation(): ContentTranslationInterface
    {
        Assert::notNull($this->targetContentTranslation);

        return $this->targetContentTranslation;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function isPermanent(): bool
    {
        Assert::boolean($this->permanent);

        return $this->permanent;
    }
}
