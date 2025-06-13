<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Extension;

use Symfony\Component\Asset\Context\RequestStackContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $publicUploadsDirectory,
        private readonly RequestStackContext $requestStackContext
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAsset'], ['is_safe' => ['html']]),
        ];
    }

    public function getUploadedAsset(string $filename): string
    {
        return sprintf(
            '%s%s%s',
            $this->requestStackContext->getBasePath(),
            $this->publicUploadsDirectory,
            $filename
        );
    }
}
