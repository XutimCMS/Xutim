<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Config\Layout;

readonly class Layout
{
    public function __construct(
        /**
         * The path of the layout folder.
         */
        public string $path,

        /**
         * The unique code across all the possible existing themes.
         */
        public string $code,

        /**
         * The name of the theme, that is displayed in the admin interface.
         */
        public string $name,

        /**
         * Base64 encoded image.
         */
        public ?string $image = null,

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
