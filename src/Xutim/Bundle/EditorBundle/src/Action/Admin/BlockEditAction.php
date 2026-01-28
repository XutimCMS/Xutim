<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Action\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;
use Xutim\EditorBundle\Form\BlockFormFactory;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockEditAction
{
    public function __construct(
        private readonly ContentBlockRepository $blockRepository,
        private readonly BlockFormFactory $formFactory,
        private readonly FormFactoryInterface $symfonyFormFactory,
        private readonly Environment $twig,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->security->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $block = $this->blockRepository->find($id);
        if ($block === null) {
            throw new NotFoundHttpException('Block not found');
        }

        $formTypeClass = $this->formFactory->getFormTypeClass($block);
        /** @phpstan-ignore argument.type */
        $form = $this->symfonyFormFactory->create($formTypeClass, $block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blockRepository->save($block, true);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return new Response($this->twig->render('@XutimEditor/admin/block/_edit_stream.html.twig', [
                    'block' => $block,
                ]));
            }

            return new Response($this->twig->render('@XutimEditor/admin/block/_preview.html.twig', [
                'block' => $block,
            ]));
        }

        return new Response($this->twig->render('@XutimEditor/admin/block/_edit_form.html.twig', [
            'block' => $block,
            'form' => $form->createView(),
        ]));
    }
}
