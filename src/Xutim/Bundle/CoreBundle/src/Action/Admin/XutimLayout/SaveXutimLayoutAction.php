<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\XutimLayout;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Config\Layout\Definition\LayoutDefinitionRegistry;
use Xutim\CoreBundle\Form\Admin\Dto\LayoutValuesDto;
use Xutim\CoreBundle\Form\Admin\LayoutFormType;

/**
 * Accepts a POST submission from the xutimLayout editor.js tool and
 * returns the normalized values as JSON. Nothing is persisted here —
 * values are stored inline in the editor.js JSON blob on the caller
 * side and saved when the content translation is saved.
 */
class SaveXutimLayoutAction extends AbstractController
{
    public function __construct(
        private readonly LayoutDefinitionRegistry $registry,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        $definition = $this->registry->getByCode($code);
        if ($definition === null) {
            throw $this->createNotFoundException(sprintf('Unknown xutim layout "%s"', $code));
        }

        $form = $this->createForm(LayoutFormType::class, new LayoutValuesDto(), [
            'layout_definition' => $definition,
        ]);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new JsonResponse([
                'ok' => false,
                'errors' => ['_global' => ['Form was not submitted']]
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            return new JsonResponse([
                'ok' => false,
                'errors' => $this->collectFormErrors($form), // @phpstan-ignore argument.type (invariant generic)
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var LayoutValuesDto $dto */
        $dto = $form->getData();

        return new JsonResponse([
            'ok' => true,
            'layoutCode' => $code,
            'values' => $dto->values,
        ]);
    }

    /**
     * @param  FormInterface<mixed>        $form
     * @return array<string, list<string>>
     */
    private function collectFormErrors(FormInterface $form): array
    {
        /** @var array<string, list<string>> $errors */
        $errors = [];

        $globalErrors = [];
        foreach ($form->getErrors() as $error) {
            $globalErrors[] = $error->getMessage();
        }
        if ($globalErrors !== []) {
            $errors['_global'] = $globalErrors;
        }

        foreach ($form->all() as $name => $child) {
            $childErrors = [];
            foreach ($child->getErrors(true) as $error) {
                $childErrors[] = $error->getMessage();
            }
            if ($childErrors !== []) {
                $errors[(string) $name] = $childErrors;
            }
        }

        return $errors;
    }
}
