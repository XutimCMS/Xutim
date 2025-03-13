<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Traversable;
use Xutim\CoreBundle\Dto\Admin\ContentTranslation\ContentTranslationDto;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Validator\UniqueSlugLocale;

/**
 * @template-extends AbstractType<ContentTranslationDto>
 * @template-implements DataMapperInterface<ContentTranslationDto>
 */
class ContentTranslationType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ContentTranslation|null $existingTranslation */
        $existingTranslation = $options['existing_translation'];
        $builder
            ->add('locale', HiddenType::class, [
                'label' => new TranslatableMessage('language', [], 'admin'),
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
                    new UniqueSlugLocale($existingTranslation)
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
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'existing_translation' => null,
        ]);

        $resolver->setAllowedTypes('existing_translation', ['null', ContentTranslation::class]);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            // Set default locale depending on translator!
            return;
        }

        // invalid data type
        if (!$viewData instanceof ContentTranslationDto) {
            throw new UnexpectedTypeException($viewData, ContentTranslationDto::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['preTitle']->setData($viewData->preTitle);
        $forms['title']->setData($viewData->title);
        $forms['subTitle']->setData($viewData->subTitle);
        $forms['slug']->setData($viewData->slug);
        $forms['content']->setData(json_encode($viewData->content, JSON_THROW_ON_ERROR));
        $forms['description']->setData($viewData->description);
        $forms['locale']->setData($viewData->locale);
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
        /** @var string $language */
        $language = $forms['locale']->getData();

        $viewData = new ContentTranslationDto($preTitle ?? '', $title, $subTitle ?? '', $slug, $content, $description ?? '', $language);
    }
}
