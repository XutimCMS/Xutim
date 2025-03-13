<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Traversable;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Dto\Admin\Article\ArticleDto;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Validator\UniqueSlugLocale;

/**
 * @template-extends AbstractType<ArticleDto>
 * @template-implements DataMapperInterface<ArticleDto>
 */
class ArticleType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentContext $contentContext,
        private readonly PageRepository $pageRepository,
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $update = array_key_exists('data', $options) === true;

        $locales = $this->siteContext->getLocales();
        $localeChoices = array_combine($locales, $locales);
        $builder
            ->add('layout', ChoiceType::class, [
                'required' => false,
                'choices' => $this->layoutLoader->getArticleLayouts(),
                'choice_label' => fn (?Layout $item) => $item->name ?? '',
                'choice_value' => fn (?Layout $item) => $item->code ?? '',
                'choice_attr' => function (?Layout $choice, string $key, string $value) {
                    return [
                        'data-image' => $choice->image ?? ''
                    ];
                },
                'expanded' => false,
                'multiple' => false
            ])
            ->add('preTitle', TextType::class, [
                'label' => new TranslatableMessage('intro title', [], 'admin'),
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'label' => new TranslatableMessage('title', [], 'admin'),
                'constraints' => [
                    new Length(['min' => 3]),
                ]
            ])
            ->add('subTitle', TextType::class, [
                'label' => new TranslatableMessage('subtitle', [], 'admin'),
                'required' => false,
            ])
            ->add('slug', TextType::class, [
                'label' => new TranslatableMessage('slug', [], 'admin'),
                'attr' => [
                    'readonly' => 'readonly',
                    'class' => 'text-bg-light'
                ],
                'constraints' => [
                    new Length(['min' => 1]),
                    new NotNull(),
                    new UniqueSlugLocale()
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => new TranslatableMessage('description', [], 'admin'),
                'required' => false,
                'attr' => [
                    'hidden' => true
                ],
                'label_attr' => [
                    'hidden' => 'hidden'
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => new TranslatableMessage('content', [], 'admin'),
                'required' => false,
                'attr' => [
                    'hidden' => 'hidden'
                ],
                'label_attr' => [
                    'hidden' => 'hidden'
                ],
            ])
//            ->add('locales', ChoiceType::class, [
//                'label' => new TranslatableMessage('locales'),
//                'choices' => $localeChoices,
//                'multiple' => true,
//                'expanded' => true,
//                'attr' => [
//                    'data-controller' => 'tom-select'
//                ]
//            ])

            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('Translation reference', [], 'admin'),
                'choices' => $localeChoices,
                'preferred_choices' => ['en', 'fr'],
                'disabled' => $update,
            ]);
        $builder
            ->add('page', ChoiceType::class, [
                'choices' => array_flip($this->pageRepository->findAllPaths()),
                'label' => new TranslatableMessage('In page', [], 'admin'),
                'required' => true,
            ])
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            $forms = iterator_to_array($forms);
            $locale = $this->contentContext->getLanguage();
            $forms['locale']->setData($locale);
            return;
        }

        // invalid data type
        if (!$viewData instanceof ArticleDto) {
            throw new UnexpectedTypeException($viewData, ArticleDto::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        // $forms['title']->setData($viewData->title);
        // $forms['slug']->setData($viewData->slug);
        $forms['content']->setData('[]');
        // $forms['description']->setData($viewData->description);
        // $forms['locale']->setData($viewData->locale);
        // $forms['page']->setData($viewData->page);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var ?string $pageId */
        $pageId = $forms['page']->getData();
        $page = $pageId !== null ? $this->pageRepository->find($pageId) : null;
        if ($page === null) {
            throw new TransformationFailedException(
                sprintf(
                    'The selected page "%s" does not exist.',
                    $pageId
                )
            );
        }

        /** @var ?Layout $layout */
        $layout = $forms['layout']->getData();
        /** @var string $layoutCode */
        $layoutCode = $layout !== null ? $layout->code : '';
        /** @var string|null $preTitle */
        $preTitle = $forms['preTitle']->getData();
        /** @var string $title */
        $title = $forms['title']->getData();
        /** @var string|null $subTitle */
        $subTitle = $forms['subTitle']->getData();
        /** @var string $slug */
        $slug = $forms['slug']->getData();
        /** @var string $jsonContent */
        $jsonContent = $forms['content']->getData();
        /**
         * @var array{}|array{
         *     time: int,
         *     blocks: array{}|array<array{id: string, type: string, data: array<string, mixed>}>,
         *     version: string
         * } $content
         */
        $content = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        /** @var string|null $description */
        $description = $forms['description']->getData();
        /** @var string $locale */
        $locale = $forms['locale']->getData();

        $viewData = new ArticleDto($layoutCode, $preTitle ?? '', $title, $subTitle ?? '', $slug, $content, $description ?? '', $locale, $page);
    }
}
