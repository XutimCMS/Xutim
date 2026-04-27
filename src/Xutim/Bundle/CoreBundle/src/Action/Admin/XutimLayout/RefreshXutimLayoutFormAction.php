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
use Xutim\SecurityBundle\Security\UserRoles;

/**
 * Re-renders the xutimLayout edit form with submitted values applied.
 * Called by the editor.js tool when a user changes a union's `type`
 * select — the form handleRequest triggers PRE_SUBMIT listeners that
 * rebuild the matching `value` field for the new type.
 *
 * Does NOT validate or persist: errors are ignored, we just want the
 * current HTML for the new state.
 */
class RefreshXutimLayoutFormAction extends AbstractController
{
    public function __construct(
        private readonly LayoutDefinitionRegistry $registry,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);

        $definition = $this->registry->getByCode($code);
        if ($definition === null) {
            throw $this->createNotFoundException(sprintf('Unknown xutim layout "%s"', $code));
        }

        $form = $this->createForm(LayoutFormType::class, new LayoutValuesDto(), [
            'layout_definition' => $definition,
            'action' => $this->router->generate('admin_xutim_layout_save', ['code' => $code]),
        ]);

        $form->handleRequest($request);

        return $this->render('@XutimCore/admin/xutim_layout/edit_form.html.twig', [
            'form' => $form,
            'definition' => $definition,
        ]);
    }
}
