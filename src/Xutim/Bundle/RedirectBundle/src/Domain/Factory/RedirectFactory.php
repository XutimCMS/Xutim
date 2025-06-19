<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Domain\Factory;

use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\RedirectBundle\Domain\Model\RedirectInterface;

class RedirectFactory implements RedirectFactoryInterface
{
    public function __construct(
        private readonly string $redirectClass,
    ) {
        if (!class_exists($redirectClass)) {
            throw new \InvalidArgumentException(sprintf('redirect class "%s" does not exist.', $redirectClass));
        }
    }

    public function create(
        string $source,
        ContentTranslationInterface $targetContentTranslation,
        ?string $locale = null,
        bool $permanent = false,
    ): RedirectInterface {
        /** @var RedirectInterface $redirect */
        $redirect = new ($this->redirectClass)($source, $targetContentTranslation, $locale, $permanent);

        return $redirect;
    }
}
