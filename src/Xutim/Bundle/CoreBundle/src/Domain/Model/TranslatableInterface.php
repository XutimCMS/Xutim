<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Domain\Model;

/**
 * @template T of LocaleAwareInterface
 */
interface TranslatableInterface
{
    /**
     * @return iterable<array-key, T>
     */
    public function getTranslations(): iterable;
}
