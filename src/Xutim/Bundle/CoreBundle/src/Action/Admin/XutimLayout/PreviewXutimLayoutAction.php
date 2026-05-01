<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\XutimLayout;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;

/**
 * Renders a minimal standalone HTML page containing only the live
 * xutimLayout output for the current values. Used as the `srcdoc`
 * of an iframe inside the editor.js xutim-layout tool so content
 * managers see the actual public-facing render of each block without
 * leaving the admin.
 *
 * Accepts the current `data.values` as a raw JSON body (same shape as
 * editor.js storage). No form round-trip, no validation — the layout
 * template must tolerate partial/missing values.
 */
class PreviewXutimLayoutAction extends AbstractController
{
    public function __construct(
        private readonly LayoutDefinitionRegistry $registry,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createAccessDeniedException();
        }

        $definition = $this->registry->getByCode($code);
        if ($definition === null) {
            throw $this->createNotFoundException(sprintf('Unknown xutim layout "%s"', $code));
        }

        $values = [];
        $body = $request->getContent();
        if ($body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                /** @var array<string, mixed> $decoded */
                $values = $decoded;
            }
        }

        $fragmentData = [
            'layoutCode' => $definition->getCode(),
            'values' => $values,
        ];

        return $this->render('@XutimCore/admin/xutim_layout/preview_wrapper.html.twig', [
            'fragmentData' => $fragmentData,
            'definition' => $definition,
            'editable' => $request->query->getBoolean('edit'),
        ]);
    }
}
