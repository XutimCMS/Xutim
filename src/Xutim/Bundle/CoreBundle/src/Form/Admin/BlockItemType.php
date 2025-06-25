<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Traversable;
use Xutim\CoreBundle\Config\Layout\Block\BlockOptionCollection;
use Xutim\CoreBundle\Config\Layout\Block\Option\ArticleBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\ColorBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\FileBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\ImageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\LinkBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\MapCoordinatesBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\PageBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\SnippetBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TagBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\TextBlockItemOption;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\Coordinates;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;
use Xutim\CoreBundle\Repository\PageRepository;

/**
 * @template-extends AbstractType<BlockItemDto>
 * @template-implements DataMapperInterface<BlockItemDto>
 */
class BlockItemType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly string $articleClass,
        private readonly string $fileClass,
        private readonly string $snippetClass,
        private readonly string $tagClass,
        private readonly PageRepository $pageRepository,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'block_options' => new BlockOptionCollection([]),
        ]);

        $resolver->setAllowedTypes('block_options', ['object']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var BlockOptionCollection $blockOptions */
        $blockOptions = $options['block_options'];


        if ($blockOptions->hasOption(ArticleBlockItemOption::class)) {
            $builder
                ->add('article', EntityType::class, [
                    'class' => $this->articleClass,
                    'label' => new TranslatableMessage('article', [], 'admin'),
                    'required' => false,
                    'choice_value' => 'id',
                    'choice_label' => function (ArticleInterface $article) {
                        return sprintf(
                            '%s',
                            $article->getTitle()
                        );
                    }
                ]);
        }

        if ($blockOptions->hasOption(PageBlockItemOption::class)) {
            $builder
                ->add('page', ChoiceType::class, [
                    'choices' => array_flip($this->pageRepository->findAllPaths()),
                    'label' => new TranslatableMessage('page', [], 'admin'),
                    'required' => false,
                ]);
        }

        if ($blockOptions->hasOption(FileBlockItemOption::class) || $blockOptions->hasOption(ImageBlockItemOption::class)) {
            $builder
                ->add('file', EntityType::class, [
                    'class' => $this->fileClass,
                    'choice_label' => 'id',
                    'placeholder' => new TranslatableMessage('select file', [], 'admin'),
                    'required' => false,
                    'attr' => ['data-controller' => 'media-field'],
                    'row_attr' => ['class' => 'd-none'],
                ])
                ->add('fileDescription', TextType::class, [
                    'label' => 'File description',
                    'required' => false,
                ]);
        }

        if ($blockOptions->hasOption(SnippetBlockItemOption::class)) {
            $builder
                ->add('snippet', EntityType::class, [
                    'class' => $this->snippetClass,
                    'label' => 'snippet',
                    'required' => false,
                ]);
        }
        if ($blockOptions->hasOption(TagBlockItemOption::class)) {
            $builder
                ->add('tag', EntityType::class, [
                    'class' => $this->tagClass,
                    'label' => 'tag',
                    'required' => false,

                ]);
        }

        if ($blockOptions->hasOption(TextBlockItemOption::class)) {
            $builder
                ->add('text', TextType::class, [
                    'label' => 'Text',
                    'required' => false,
                ]);
        }

        if ($blockOptions->hasOption(LinkBlockItemOption::class)) {
            $builder
                ->add('link', TextType::class, [
                    'label' => 'Link',
                    'required' => false,
                ]);
        }
        if ($blockOptions->hasOption(ColorBlockItemOption::class)) {
            $builder
                ->add('color', ColorType::class, [
                    'label' => new TranslatableMessage('color', [], 'admin'),
                    'required' => false,
                    'constraints' => [
                        new Length(['max' => 6])
                    ]
                ]);
        }

        if ($blockOptions->hasOption(MapCoordinatesBlockItemOption::class)) {
            $builder
                ->add('latitude', NumberType::class, [
                    'required' => false,
                    'scale' => 6
                ])
                ->add('longitude', NumberType::class, [
                    'required' => false,
                    'scale' => 6
                ]);
        }
        $builder
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this)
        ;
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        if (!$viewData instanceof BlockItemDto) {
            throw new UnexpectedTypeException($viewData, BlockItemDto::class);
        }

        $forms = iterator_to_array($forms);

        if (array_key_exists('page', $forms)) {
            $forms['page']->setData($viewData->page?->getId());
        }
        if (array_key_exists('file', $forms)) {
            $forms['file']->setData($viewData->file);
            $forms['fileDescription']->setData($viewData->fileDescription);
        }
        if (array_key_exists('snippet', $forms)) {
            $forms['snippet']->setData($viewData->snippet);
        }
        if (array_key_exists('tag', $forms)) {
            $forms['tag']->setData($viewData->tag);
        }
        if (array_key_exists('article', $forms)) {
            $forms['article']->setData($viewData->article);
        }
        if (array_key_exists('text', $forms)) {
            $forms['text']->setData($viewData->text);
        }
        if (array_key_exists('link', $forms)) {
            $forms['link']->setData($viewData->link);
        }
        if (array_key_exists('color', $forms)) {
            $forms['color']->setData($viewData->color);
        }
        if (array_key_exists('latitude', $forms)) {
            $forms['latitude']->setData($viewData->coordinates?->latitude);
            $forms['longitude']->setData($viewData->coordinates?->longitude);
        }
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        if (array_key_exists('page', $forms)) {
            /** @var ?string $pageId */
            $pageId = $forms['page']->getData();
            $page = $pageId !== null ? $this->pageRepository->find($pageId) : null;
        }
        if (array_key_exists('article', $forms)) {
            /** @var ArticleInterface|null $article */
            $article = $forms['article']->getData();
        }
        if (array_key_exists('file', $forms)) {
            /** @var FileInterface|null $file */
            $file = $forms['file']->getData();
            /** @var string|null $description */
            $description = $forms['fileDescription']->getData();
        }
        if (array_key_exists('snippet', $forms)) {
            /** @var SnippetInterface $snippet */
            $snippet = $forms['snippet']->getData();
        }
        if (array_key_exists('tag', $forms)) {
            /** @var TagInterface $tag */
            $tag = $forms['tag']->getData();
        }
        if (array_key_exists('text', $forms)) {
            /** @var string|null $text */
            $text = $forms['text']->getData();
        }
        if (array_key_exists('link', $forms)) {
            /** @var string|null $link */
            $link = $forms['link']->getData();
        }
        if (array_key_exists('color', $forms)) {
            /** @var string|null $color */
            $color = $forms['color']->getData();
        }

        if (array_key_exists('latitude', $forms)) {
            /** @var float|null $latitude */
            $latitude = $forms['latitude']->getData();
            /** @var float|null $longitude */
            $longitude = $forms['longitude']->getData();
            $coords = $latitude !== null && $longitude !== null ? new Coordinates($latitude, $longitude) : null;
        }

        $viewData = new BlockItemDto(
            $page ?? null,
            $article ?? null,
            $file ?? null,
            $snippet ?? null,
            $tag ?? null,
            null,
            $text ?? null,
            $link ?? null,
            $color ?? null,
            $description ?? null,
            $coords ?? null
        );
    }
}
