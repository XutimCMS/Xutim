<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Traversable;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Form\Admin\Dto\MenuItemDto;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Repository\SnippetRepository;

/**
 * @template-extends AbstractType<MenuItemDto>
 * @template-implements DataMapperInterface<MenuItemDto>
 */
class MenuItemType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly ArticleRepository $articleRepository,
        private readonly SnippetRepository $snippetRepository,
        private readonly ContentContext $contentContext,
        private readonly string $articleClass,
        private readonly string $snippetClass,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $this->contentContext->getLanguage();
        $builder
            ->add('page', ChoiceType::class, [
                'choices' => array_flip($this->pageRepository->findAllPaths()),
                'label' => new TranslatableMessage('Page', [], 'admin'),
                'required' => false,
                'attr' => [
                    'data-controller' => 'tom-select'
                ]
            ])
            ->add('article', EntityType::class, [
                'class' => $this->articleClass,
                'choice_label' => fn (Article $article): string =>
                $article->getTranslationByLocaleOrDefault($locale)->getTitle(),
                'label' => new TranslatableMessage('Article', [], 'admin'),
                'required' => false,
                'attr' => [
                    'data-controller' => 'tom-select'
                ]
            ])
            ->add('pageLink', ChoiceType::class, [
                'choices' => array_flip($this->pageRepository->findAllPaths()),
                'label' => new TranslatableMessage('Overwrite page link', [], 'admin'),
                'required' => false,
                'attr' => [
                    'data-controller' => 'tom-select'
                ]
            ])
            ->add('anchorSnippet', EntityType::class, [
                'class' => $this->snippetClass,
                'choice_label' => fn (SnippetInterface $snippet): string => $snippet->getCode(),
                'label' => new TranslatableMessage('Anchor snippet', [], 'admin'),
                'required' => false,
                'attr' => [
                    'data-controller' => 'tom-select'
                ]
            ])
            ->add('hasLink', CheckboxType::class, [
                'label' => new TranslatableMessage('Should have a link', [], 'admin'),
                'required' => false,
            ])
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof MenuItemDto) {
            throw new UnexpectedTypeException($viewData, MenuItemDto::class);
        }

        $forms = iterator_to_array($forms);
        $forms['hasLink']->setData($viewData->hasLink);
        $forms['page']->setData($viewData->page?->getId()->toRfc4122());
        $forms['article']->setData($viewData->article);
        $forms['pageLink']->setData($viewData->overwritePage?->getId()->toRfc4122());
        $forms['anchorSnippet']->setData($viewData->snippetAnchor);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var ?string $pageId */
        $pageId = $forms['page']->getData();
        $page = $pageId !== null ? $this->pageRepository->find($pageId) : null;

        /** @var ?string $articleId */
        $articleId = $forms['article']->getData();
        $article = $articleId !== null ? $this->articleRepository->find($articleId) : null;

        /** @var ?string $overwritePageId */
        $overwritePageId = $forms['pageLink']->getData();
        $overwritePage = $overwritePageId !== null ? $this->pageRepository->find($overwritePageId) : null;

        /** @var ?string $anchorId */
        $anchorId = $forms['anchorSnippet']->getData();
        $anchorSnippet = $anchorId !== null ? $this->snippetRepository->find($anchorId) : null;


        // We don't use it at the moment.
        // @var bool $hasLink
        /* $hasLink = $forms['hasLink']->getData(); */

        $viewData = new MenuItemDto(true, $page, $article, $overwritePage, $anchorSnippet);
    }
}
