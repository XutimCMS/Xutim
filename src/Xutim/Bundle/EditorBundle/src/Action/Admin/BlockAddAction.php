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
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\EditorBundle\Domain\Factory\ContentBlockFactory;
use Xutim\EditorBundle\Form\BlockFormFactory;
use Xutim\EditorBundle\Repository\ContentBlockRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockAddAction
{
    public function __construct(
        private readonly ContentDraftRepository $draftRepository,
        private readonly ContentBlockRepository $blockRepository,
        private readonly ContentBlockFactory $blockFactory,
        private readonly BlockFormFactory $formFactory,
        private readonly FormFactoryInterface $symfonyFormFactory,
        private readonly Environment $twig,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request, string $draftId): Response
    {
        if (!$this->security->isGranted(UserRoles::ROLE_EDITOR)) {
            throw new AccessDeniedHttpException();
        }

        $draft = $this->draftRepository->find($draftId);
        if ($draft === null) {
            throw new NotFoundHttpException('Draft not found');
        }

        $blockType = $request->query->getString('type', 'paragraph');
        $position = $request->query->getInt('position', 0);
        $parentId = $request->query->getString('parent');
        $slot = $request->query->getInt('slot');

        $parent = null;
        if ($parentId !== '') {
            $parent = $this->blockRepository->find($parentId);
        }

        $block = $this->blockFactory->create($blockType, $draft, $parent, $slot > 0 ? $slot : null, $position);
        $this->blockRepository->save($block, true);

        $formTypeClass = $this->formFactory->getFormTypeClassByType($blockType);
        /** @phpstan-ignore argument.type */
        $form = $this->symfonyFormFactory->create($formTypeClass, $block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blockRepository->save($block, true);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return new Response($this->twig->render('@XutimEditor/admin/block/_add_stream.html.twig', [
                    'block' => $block,
                    'draft' => $draft,
                ]));
            }

            return new Response($this->twig->render('@XutimEditor/admin/block/_preview.html.twig', [
                'block' => $block,
            ]));
        }

        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        return new Response($this->twig->render('@XutimEditor/admin/block/_add_form_stream.html.twig', [
            'block' => $block,
            'draft' => $draft,
            'form' => $form->createView(),
        ]));
    }
}
