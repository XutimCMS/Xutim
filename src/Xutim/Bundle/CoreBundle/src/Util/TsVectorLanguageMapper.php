<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Util;

use Symfony\Component\Intl\Locale;

final readonly class TsVectorLanguageMapper
{
    private const SUPPORTED_DICTIONARIES = [
            'ar' => 'arabic',
            'be' => 'russian',
            'bg' => 'simple',
            'cs' => 'simple',
            'zh' => 'simple',
            'da' => 'danish',
            'de' => 'german',
            'et' => 'simple',
            'el' => 'greek',
            'en' => 'english',
            'es' => 'spanish',
            'ca' => 'spanish',
            'fr' => 'french',
            'fi' => 'finnish',
            'sw' => 'simple',
            'hr' => 'simple',
            'id' => 'indonesian',
            'it' => 'italian',
            'ja' => 'simple',
            'ko' => 'simple',
            'lv' => 'simple',
            'lt' => 'lithuanian',
            'hu' => 'hungarian',
            'nl' => 'dutch',
            'no' => 'norwegian',
            'pl' => 'simple',
            'pt' => 'portuguese',
            'ro' => 'romanian',
            'ru' => 'russian',
            'sr' => 'serbian',
            'sk' => 'simple',
            'sl' => 'simple',
            'sv' => 'swedish',
            'ta' => 'simple',
            'uk' => 'russian',
            'vi' => 'simple',
        ];

    public static function getDictionary(string $locale): string
    {
        $language = Locale::getPrimaryLanguage($locale);
        if ($language === null) {
            return 'simple';
        }

        return self::SUPPORTED_DICTIONARIES[$language] ?? 'simple';
    }
}
