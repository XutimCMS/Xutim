<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
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

        $mainLocales = $this->siteContext->getMainLocales();
        $preferredLocaleChoices = array_combine($mainLocales, $mainLocales);
        $locales = $this->siteContext->getAllLocales();
        $localeChoices = array_combine(array_map(fn ($locale) => Languages::getName($locale), $locales), $locales);

        $sortedMainLocales = $mainLocales;
        sort($sortedMainLocales);
        $sortedExtendedLocales = $this->siteContext->getExtendedContentLocales();
        sort($sortedExtendedLocales);

        $translationLocaleChoices = [];
        foreach ($sortedMainLocales as $locale) {
            $translationLocaleChoices['main languages'][$locale] = $locale;
        }
        foreach ($sortedExtendedLocales as $locale) {
            $translationLocaleChoices['extended languages'][$locale] = $locale;
        }

        $builder
            ->add('preTitle', TextType::class, [
                'label' => new TranslatableMessage('intro title', [], 'admin'),
                'required' => false
            ])
            ->add('title', TextType::class, [
                'label' => new TranslatableMessage('title', [], 'admin'),
                'constraints' => [
                    new Length(['min' => 1]),
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
                    new NotBlank(),
                    new Length(['min' => 3]),
                    new UniqueSlugLocale(),
                    new Regex(['pattern' => '/^[a-z0-9]+(-[a-z0-9]+)*$/', 'message' => 'The slug should be written in kebab-case.'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => new TranslatableMessage('description', [], 'admin'),
                'required' => false,
                'help' => new TranslatableMessage('The description appears in search engine results and when the page is shared on social media. Summarize the article in 1–2 sentences (max. 160 characters).', [], 'admin')
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
            ->add('locale', LanguageType::class, [
                'label' => new TranslatableMessage('Translation reference', [], 'admin'),
                'choices' => $localeChoices,
                'preferred_choices' => $preferredLocaleChoices,
                'choice_loader' => null,
                'disabled' => $update,
            ])
            ->add('parent', ChoiceType::class, [
                'choices' => array_flip($this->pageRepository->findAllPaths()),
                'label' => new TranslatableMessage('In page', [], 'admin'),
                'required' => false,
            ])
            ->add('featuredImage', HiddenType::class, [
                'required' => false,
            ])
            ->add('allTranslationLocales', ChoiceType::class, [
                'label' => new TranslatableMessage('translate into', [], 'admin'),
                'choices' => [
                    'all languages' => true,
                    'specific languages' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'placeholder' => false,
                'constraints' => [new NotNull()],
            ])
            ->add('translationLocales', LanguageType::class, [
                'label' => new TranslatableMessage('select languages this content can be translated into', [], 'admin'),
                'multiple' => true,
                'expanded' => true,
                'choices' => $translationLocaleChoices,
                'choice_loader' => null,
                'choice_label' => fn (string $locale) => strtoupper($locale),
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this)
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $form = $event->getForm();
                $allTranslationLocales = $form->get('allTranslationLocales')->getData();
                if ($allTranslationLocales !== false) {
                    return;
                }
                $locale = $form->get('locale')->getData();
                /** @var list<string> $translationLocales */
                $translationLocales = $form->get('translationLocales')->getData();
                if (!in_array($locale, $translationLocales, true)) {
                    $form->get('translationLocales')->addError(
                        new FormError('The translation reference language must be included in the selected languages.')
                    );
                }
            });
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            $forms = iterator_to_array($forms);
            $locale = $this->contentContext->getLanguage();
            $forms['locale']->setData($locale);
            $forms['allTranslationLocales']->setData(null);
            $forms['translationLocales']->setData([]);
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
        $title = $forms['title']->getData() ?? '';
        /** @var string $subTitle */
        $subTitle = $forms['subTitle']->getData() ?? '';
        /** @var string $slug */
        $slug = $forms['slug']->getData() ?? '';
        /** @var string|null $description */
        $description = $forms['description']->getData();
        /** @var string $jsonContent */
        $jsonContent = $forms['content']->getData() ?? '[]';
        /** @var EditorBlock $content */
        $content = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        /** @var string $language */
        $language = $forms['locale']->getData() ?? '';

        /** @var ?string $featuredImageId */
        $featuredImageId = $forms['featuredImage']->getData();
        $imageUuid = null;
        if ($featuredImageId !== null) {
            $imageUuid = new UuidV4($featuredImageId);
        }

        /** @var ?bool $allTranslationLocales */
        $allTranslationLocales = $forms['allTranslationLocales']->getData();
        /** @var list<string> $translationLocales */
        $translationLocales = $forms['translationLocales']->getData();

        $viewData = new PageDto(
            $layout,
            $preTitle,
            $title,
            $subTitle,
            $slug,
            $content,
            $description ?? '',
            $allTranslationLocales,
            $translationLocales,
            $language,
            $parent,
            $imageUuid
        );
    }
}
