<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\XutimLayout;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;
use Xutim\CoreBundle\Form\Admin\Dto\LayoutValuesDto;
use Xutim\CoreBundle\Form\Admin\LayoutFormType;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;

/**
 * Returns the inline form HTML for editing a xutimLayout block's values.
 * Seeded with current values passed via the `?values=` query parameter
 * (JSON-encoded). Intended to be embedded inside the editor.js block
 * wrapper via an AJAX fetch by the xutimLayout tool.
 */
class EditXutimLayoutFormAction extends AbstractController
{
    public function __construct(
        private readonly LayoutDefinitionRegistry $registry,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        $definition = $this->registry->getByCode($code);
        if ($definition === null) {
            throw $this->createNotFoundException(sprintf('Unknown xutim layout "%s"', $code));
        }

        $rawValues = $request->query->get('values');
        $initialValues = [];
        if (is_string($rawValues) && $rawValues !== '') {
            $decoded = json_decode($rawValues, true);
            if (is_array($decoded)) {
                /** @var array<string, mixed> $decoded */
                $initialValues = $decoded;
            }
        }

        $form = $this->createForm(LayoutFormType::class, new LayoutValuesDto($initialValues), [
            'layout_definition' => $definition,
            'action' => $this->router->generate('admin_xutim_layout_save', ['code' => $code]),
        ]);

        return $this->render('@XutimCore/admin/xutim_layout/edit_form.html.twig', [
            'form' => $form,
            'definition' => $definition,
        ]);
    }
}
