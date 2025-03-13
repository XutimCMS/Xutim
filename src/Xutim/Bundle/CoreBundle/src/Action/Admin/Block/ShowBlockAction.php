<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Block;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Config\Layout\Block\BlockLayoutChecker;
use Xutim\CoreBundle\Entity\Block;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\BlockType;
use Xutim\CoreBundle\Form\Admin\Dto\BlockDto;

#[Route('/block/show/{id}', name: 'admin_block_show', methods: ['get'])]
class ShowBlockAction extends AbstractController
{
    public function __invoke(Request $request, Block $block, BlockLayoutChecker $layoutChecker): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);

        $form = $this->createForm(
            BlockType::class,
            BlockDto::fromBlock($block),
            ['disabled' => true]
        );

        return $this->render('@XutimCore/admin/block/block_show.html.twig', [
            'form' => $form,
            'block' => $block,
            'layoutFulFilled' => $layoutChecker->checkLayout($block)
        ]);
    }
}
