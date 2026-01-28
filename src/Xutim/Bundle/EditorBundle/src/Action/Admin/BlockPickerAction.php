<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Action\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Xutim\EditorBundle\Domain\Template\BlockTemplateRegistry;
use Xutim\EditorBundle\Entity\Block\CodeBlock;
use Xutim\EditorBundle\Entity\Block\EmbedBlock;
use Xutim\EditorBundle\Entity\Block\HeadingBlock;
use Xutim\EditorBundle\Entity\Block\ImageBlock;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;
use Xutim\EditorBundle\Entity\Block\ListItemBlock;
use Xutim\EditorBundle\Entity\Block\ParagraphBlock;
use Xutim\EditorBundle\Entity\Block\QuoteBlock;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\SecurityBundle\Security\UserRoles;

final class BlockPickerAction
{
    private const array BLOCK_TYPES = [
        [
            'type' => ParagraphBlock::TYPE,
            'label' => 'Paragraph',
            'description' => 'Plain text paragraph',
            'icon' => 'align-left',
            'category' => 'text',
        ],
        [
            'type' => HeadingBlock::TYPE,
            'label' => 'Heading',
            'description' => 'Section heading (H1-H6)',
            'icon' => 'h-1',
            'category' => 'text',
        ],
        [
            'type' => ListItemBlock::TYPE,
            'label' => 'List Item',
            'description' => 'Bullet, numbered, or checklist',
            'icon' => 'list',
            'category' => 'text',
        ],
        [
            'type' => QuoteBlock::TYPE,
            'label' => 'Quote',
            'description' => 'Quotation with attribution',
            'icon' => 'blockquote',
            'category' => 'text',
        ],
        [
            'type' => ImageBlock::TYPE,
            'label' => 'Image',
            'description' => 'Image with optional caption',
            'icon' => 'photo',
            'category' => 'media',
        ],
        [
            'type' => EmbedBlock::TYPE,
            'label' => 'Embed',
            'description' => 'YouTube, Vimeo, or other embeds',
            'icon' => 'brand-youtube',
            'category' => 'media',
        ],
        [
            'type' => CodeBlock::TYPE,
            'label' => 'Code',
            'description' => 'Code snippet with syntax highlighting',
            'icon' => 'code',
            'category' => 'media',
        ],
        [
            'type' => LayoutBlock::TYPE,
            'label' => 'Layout',
            'description' => 'Multi-column layout container',
            'icon' => 'columns',
            'category' => 'layout',
        ],
    ];

    public function __construct(
        private readonly ContentDraftRepository $draftRepository,
        private readonly BlockTemplateRegistry $templateRegistry,
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

        $position = $request->query->getInt('position', 0);
        $parentId = $request->query->getString('parent');
        $slot = $request->query->getInt('slot');

        $content = $this->twig->render('@XutimEditor/admin/block/_picker.html.twig', [
            'draft' => $draft,
            'blockTypes' => self::BLOCK_TYPES,
            'templates' => $this->templateRegistry->all(),
            'position' => $position,
            'parentId' => $parentId,
            'slot' => $slot,
        ]);

        return new Response($content);
    }
}
