<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Config\Layout\Block\BlockLayoutChecker;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\BlockType;
use Xutim\CoreBundle\Form\Admin\Dto\BlockDto;
use Xutim\CoreBundle\Repository\BlockRepository;

#[Route('/block/show/{id}', name: 'admin_block_show', methods: ['get'])]
class ShowBlockAction extends AbstractController
{
    public function __construct(
        private readonly BlockRepository $blockRepo,
        private readonly BlockLayoutChecker $layoutChecker
    ) {
    }

    public function __invoke(string $id): Response
    {
        $block = $this->blockRepo->find($id);
        if ($block === null) {
            throw $this->createNotFoundException('The block does not exist');
        }
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);

        $form = $this->createForm(
            BlockType::class,
            BlockDto::fromBlock($block),
            ['disabled' => true]
        );

        return $this->render('@XutimCore/admin/block/block_show.html.twig', [
            'form' => $form,
            'block' => $block,
            'configOptions' => $this->layoutChecker->getLayoutConfig($block),
            'layoutFulFilled' => $this->layoutChecker->checkLayout($block)
        ]);
    }
}
