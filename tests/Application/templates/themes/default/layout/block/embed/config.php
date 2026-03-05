<?php

declare(strict_types=1);

use App\Config\Block\Option\EmbedUrlBlockItemOption;
use Xutim\CoreBundle\Config\Layout\LayoutConfig;

return new LayoutConfig(
    code: 'embed',
    name: 'Embed Block',
    config: [new EmbedUrlBlockItemOption()],
);
