<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Traversable;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\TagTranslationInterface;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Form\Admin\Dto\TagDto;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Validator\UniqueTagSlugLocale;

/**
 * @template-extends AbstractType<TagDto>
 * @template-implements DataMapperInterface<TagDto>
 */
class TagType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentContext $contentContext,
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TagTranslationInterface|null $existingTrans */
        $existingTrans = $options['existing_translation'];
        $update = $existingTrans !== null;

        $locales = $this->siteContext->getLocales();
        $localeChoices = array_combine($locales, $locales);
        $builder
            ->add('featuredImage', HiddenType::class, [
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', [], 'admin'),
                'constraints' => [
                    new Length(['min' => 3]),
                ]
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
                    new UniqueTagSlugLocale($existingTrans)
                ]
            ])
            ->add('color', ColorType::class, [
                'label' => new TranslatableMessage('color', [], 'admin'),
                'required' => false,
                'constraints' => [
                    new Length(['max' => 6])
                ]
            ])
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('Translation reference', [], 'admin'),
                'choices' => $localeChoices,
                'disabled' => $update,
            ])
            ->add('layout', ChoiceType::class, [
                'required' => true,
                'choices' => $this->layoutLoader->getTagLayouts(),
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
        if (!$viewData instanceof TagDto) {
            throw new UnexpectedTypeException($viewData, TagDto::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['name']->setData($viewData->name);
        $forms['slug']->setData($viewData->slug);
        $forms['locale']->setData($viewData->locale);
        $forms['color']->setData($viewData->color->getHex());
        $layout = $this->layoutLoader->getTagLayoutByCode($viewData->layout);
        $forms['layout']->setData($layout);
        $forms['featuredImage']->setData($viewData->featuredImageId);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var string $name */
        $name = $forms['name']->getData();
        /** @var string $slug */
        $slug = $forms['slug']->getData();
        /** @var string $locale */
        $locale = $forms['locale']->getData();
        /** @var ?Layout $layout */
        $layout = $forms['layout']->getData();
        /** @var ?string $featuredImageId */
        $featuredImageId = $forms['featuredImage']->getData();
        $imageUuid = null;
        if ($featuredImageId !== null) {
            $imageUuid = new UuidV4($featuredImageId);
        }

        /** @var string|null $colorVal */
        $colorVal = $forms['color']->getData();
        $color = new Color($colorVal);

        $viewData = new TagDto($name, $slug, $locale, $color, $imageUuid, $layout?->code);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'existing_translation' => null,
        ]);

        $resolver->setAllowedTypes('existing_translation', ['null', TagTranslationInterface::class]);
    }
}
