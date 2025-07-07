<?php

declare(strict_types=1);

namespace Xutim\RedirectComponent\Domain\Factory;

use Xutim\RedirectComponent\Domain\Model\RedirectInterface;

class RedirectFactory implements RedirectFactoryInterface
{
    /**
         * @param class-string<RedirectInterface> $redirectClass
         */
    public function __construct(
        private string $redirectClass
    ) {
    }

    public function create(
        string $source,
        string $target,
        bool $permanent = false
    ): RedirectInterface {
        return new $this->redirectClass($source, $target, $permanent);
    }
}
