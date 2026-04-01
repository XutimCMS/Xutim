<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Dashboard;

interface TranslationStatProvider
{
    /**
     * @param list<string> $locales user's translation locales
     */
    public function getStat(array $locales, string $referenceLocale): TranslationStat;
}
