<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Traversable;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Dto\Admin\Page\PageDto;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Validator\UniqueSlugLocale;

/**
 * @template-extends AbstractType<PageDto>
 * @template-implements DataMapperInterface<PageDto>
 */
class PageType extends AbstractType implements DataMapperInterface
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
            ->add('preTitle', TextType::class, [
                'label' => new TranslatableMessage('intro title', [], 'admin'),
                'required' => false
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
            ->add('layout', ChoiceType::class, [
                'required' => true,
                'choices' => $this->layoutLoader->getPageLayouts(),
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
            ->add('slug', TextType::class, [
                'label' => new TranslatableMessage('slug', [], 'admin'),
                'attr' => [
                    'readonly' => 'readonly',
                    'class' => 'text-bg-light'
                ],
                'constraints' => [
                    new Length(['min' => 3]),
                    new UniqueSlugLocale(),
                    new Regex(['pattern' => '/^[a-z0-9]+(-[a-z0-9]+)*$/', 'message' => 'The slug should be written in kebab-case.'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => new TranslatableMessage('description', [], 'admin'),
                'required' => false,
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
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('Translation reference', [], 'admin'),
                'choices' => $localeChoices,
                'preferred_choices' => ['en', 'fr'],
                'disabled' => $update,
            ])
            ->add('parent', ChoiceType::class, [
                'choices' => array_flip($this->pageRepository->findAllPaths()),
                'label' => new TranslatableMessage('In page', [], 'admin'),
                'required' => false,
            ])
            ->add('color', ColorType::class, [
                'label' => new TranslatableMessage('color', [], 'admin'),
                'required' => false,
                'constraints' => [
                    new Length(['max' => 6])
                ],
                'disabled' => $update,
            ])
            ->add('featuredImage', HiddenType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
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
        if (!$viewData instanceof PageDto) {
            throw new UnexpectedTypeException($viewData, PageDto::class);
        }

        $forms = iterator_to_array($forms);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var ?string $parentId */
        $parentId = $forms['parent']->getData();
        $parent = $parentId !== null ? $this->pageRepository->find($parentId) : null;
        /** @var null|Layout $layout */
        $layout = $forms['layout']->getData();
        /** @var string $layout */
        $layout = $layout === null ? '' : $layout->code;
        /** @var string $preTitle */
        $preTitle = $forms['preTitle']->getData() ?? '';
        /** @var string $title */
        $title = $forms['title']->getData();
        /** @var string $subTitle */
        $subTitle = $forms['subTitle']->getData() ?? '';
        /** @var string $slug */
        $slug = $forms['slug']->getData();
        /** @var string|null $description */
        $description = $forms['description']->getData();
        /** @var string $jsonContent */
        $jsonContent = $forms['content']->getData();
        /** @var EditorBlock $content */
        $content = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        /** @var string $language */
        $language = $forms['locale']->getData();
        /** @var string $color */
        $color = $forms['color']->getData();

        /** @var ?string $featuredImageId */
        $featuredImageId = $forms['featuredImage']->getData();
        $imageUuid = null;
        if ($featuredImageId !== null) {
            $imageUuid = new UuidV4($featuredImageId);
        }

        $viewData = new PageDto(
            $layout,
            $color,
            $preTitle,
            $title,
            $subTitle,
            $slug,
            $content,
            $description ?? '',
            [],
            $language,
            $parent,
            $imageUuid
        );
    }
}
