<?php

declare(strict_types=1);
namespace Xutim\CoreBundle\MessageHandler\Command;

use Xutim\CoreBundle\Message\Command\GenerateSitemapCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Sitemap\SitemapGenerator;

readonly class GenerateSitemapHandler implements CommandHandlerInterface
{
    public function __construct(private readonly SitemapGenerator $generator)
    {
    }

    public function __invoke(GenerateSitemapCommand $cmd): void
    {
        $this->generator->generate();
    }
}
