<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Traversable;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Form\Admin\Dto\ContentTranslationExtendedDto;
use Xutim\CoreBundle\Validator\UniqueSlugLocale;

/**
 * @template-extends AbstractType<ContentTranslationExtendedDto>
 * @template-implements DataMapperInterface<ContentTranslationExtendedDto>
 */
class ContentTranslationExtendedType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ContentTranslation|null $existingTranslation */
        $existingTranslation = $options['existing_translation'];

        $locales = $this->siteContext->getExtendedContentLocales();
        $localeChoices = array_combine($locales, $locales);
        $builder
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('language', [], 'admin'),
                'choices' => $localeChoices,
                'required' => true,
                'constraints' => [
                    new Length(['min' => 2]),
                    new NotNull(),
                    new UniqueSlugLocale()
                ]
                
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
                    new Length(['min' => 3]),
                    new NotNull(),
                    new UniqueSlugLocale($existingTranslation),
                    new Regex(['pattern' => '/^[a-z0-9]+(-[a-z0-9]+)*$/', 'message' => 'The slug should be written in kebab-case.'])
                ]
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
            ->add('description', TextareaType::class, [
                'label' => new TranslatableMessage('description', [], 'admin'),
                'required' => false
            ])
            ->setDataMapper($this)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'existing_translation' => null,
            'locales' => []
        ]);

        $resolver->setAllowedTypes('existing_translation', ['null', ContentTranslation::class]);
        $resolver->setAllowedTypes('locales', ['string[]']);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof ContentTranslationExtendedDto) {
            throw new UnexpectedTypeException($viewData, ContentTranslationExtendedDto::class);
        }

        $forms = iterator_to_array($forms);

        $forms['preTitle']->setData($viewData->getPreTitle());
        $forms['title']->setData($viewData->getTitle());
        $forms['subTitle']->setData($viewData->getSubTitle());
        $forms['slug']->setData($viewData->getSlug());
        $forms['content']->setData($viewData->getContent());
        $forms['description']->setData($viewData->getDescription());
        $forms['locale']->setData($viewData->getLocale());
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);
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

        $viewData = new ContentTranslationExtendedDto(
            $preTitle,
            $title,
            $subTitle,
            $slug,
            $content,
            $description,
            $locale
        );
    }
}
