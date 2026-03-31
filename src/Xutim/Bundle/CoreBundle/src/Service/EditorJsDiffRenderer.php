<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Content\Diff\CanonicalContentDiffRenderer;
use Xutim\CoreBundle\Content\Transform\EditorJsToCanonicalDocumentTransformer;

final class EditorJsDiffRenderer
{
    public function __construct(
        private readonly EditorJsToCanonicalDocumentTransformer $transformer,
        private readonly CanonicalContentDiffRenderer $canonicalDiffRenderer,
    ) {
    }

    public function diffTitle(?string $old, ?string $new): string
    {
        return $this->canonicalDiffRenderer->diffText($old ?? '', $new ?? '');
    }

    public function diffDescription(?string $old, ?string $new): string
    {
        return $this->canonicalDiffRenderer->diffText($old ?? '', $new ?? '');
    }

    /**
     * @param EditorBlock $old
     * @param EditorBlock $new
     *
     * @return list<array<string, mixed>>
     */
    public function diffContent(array $old, array $new): array
    {
        return $this->canonicalDiffRenderer->diffDocuments(
            $this->transformer->transform($old),
            $this->transformer->transform($new),
        );
    }
}
