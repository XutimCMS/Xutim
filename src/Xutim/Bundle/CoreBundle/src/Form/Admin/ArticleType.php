<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Traversable;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Form\Admin\Dto\CreateArticleFormData;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Validator\UniqueSlugLocale;

/**
 * @template-extends AbstractType<CreateArticleFormData>
 * @template-implements DataMapperInterface<CreateArticleFormData>
 */
class ArticleType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentContext $contentContext,
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $update = array_key_exists('data', $options) === true;

        $locales = $this->siteContext->getLocales();
        $localeChoices = array_combine($locales, $locales);
        $builder
            ->add('featuredImage', HiddenType::class, [
                'required' => false,
            ])
            ->add('layout', ChoiceType::class, [
                'required' => true,
                'choices' => $this->layoutLoader->getArticleLayouts(),
                'choice_label' => fn (?Layout $item) => $item->name ?? '',
                'choice_value' => fn (?Layout $item) => $item->code ?? '',
                'choice_attr' => function (?Layout $choice, string $key, string $value) {
                    return [
                        'data-image' => $choice->image ?? ''
                    ];
                },
            ])
            ->add('preTitle', TextType::class, [
                'label' => new TranslatableMessage('intro title', [], 'admin'),
                'required' => false,
            ])
            ->add('title', TextType::class, [
                'required' => true,
                'label' => new TranslatableMessage('title', [], 'admin'),
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull(),
                ]
            ])
            ->add('subTitle', TextType::class, [
                'label' => new TranslatableMessage('subtitle', [], 'admin'),
                'required' => false,
            ])
            ->add('slug', TextType::class, [
                'required' => true,
                'label' => new TranslatableMessage('slug', [], 'admin'),
                'attr' => [
                    'readonly' => 'readonly',
                    'class' => 'text-bg-light'
                ],
                'constraints' => [
                    new Length(['min' => 1]),
                    new NotNull(),
                    new UniqueSlugLocale(),
                    new Regex(['pattern' => '/^[a-z0-9]+(-[a-z0-9]+)*$/', 'message' => 'The slug should be written in kebab-case.'])
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
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('Translation reference', [], 'admin'),
                'choices' => $localeChoices,
                'disabled' => $update,
            ]);
        $builder
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
        if (!$viewData instanceof CreateArticleFormData) {
            throw new UnexpectedTypeException($viewData, CreateArticleFormData::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['content']->setData('[]');
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

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
        /** @var EditorBlock $content */
        $content = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        /** @var string|null $description */
        $description = $forms['description']->getData();
        /** @var string $locale */
        $locale = $forms['locale']->getData();

        /** @var ?string $featuredImageId */
        $featuredImageId = $forms['featuredImage']->getData();
        $imageUuid = null;
        if ($featuredImageId !== null) {
            $imageUuid = new UuidV4($featuredImageId);
        }

        $viewData = new CreateArticleFormData($layoutCode, $preTitle ?? '', $title, $subTitle ?? '', $slug, $content, $description ?? '', $locale, $imageUuid);
    }
}
