<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout;

readonly class LayoutConfig
{
    public function __construct(
        /**
         * The unique code across all the possible existing themes.
         */
        public string $code,

        /**
         * The name of the theme, that is displayed in the admin interface.
         */
        public string $name,

        /**
         * The name of the preview image.
         */
        public ?string $imagePath = null,

        /**
         * Layout configuration. It depends on type of layout.
         *
         * @var list<LayoutConfigItem>
         */
        public array $config = [],

        /**
         * Cache Time To Live (in seconds).
         */
        public int $cacheTtl = 604800 // 1 week
    ) {
    }
}
