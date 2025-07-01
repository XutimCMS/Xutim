<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Traversable;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\SiteContext;

/**
 * @template-extends AbstractType<EventTranslationDto>
 * @template-implements DataMapperInterface<EventTranslationDto>
 */
class EventTranslationType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly ContentContext $contentContext
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $update = array_key_exists('data', $options) === true;

        $locales = $this->siteContext->getLocales();
        $localeChoices = array_combine($locales, $locales);
        $builder
            ->add('title', TextType::class, [
                'label' => new TranslatableMessage('title', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull()
                ]
            ])
            ->add('location', TextType::class, [
                'label' => new TranslatableMessage('location', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull()
                ]
            ])

            ->add('description', TextType::class, [
                'label' => new TranslatableMessage('description', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull()
                ]
            ])
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('Translation reference', [], 'admin'),
                'choices' => $localeChoices,
                'disabled' => true,
            ])
            ->add('submit', SubmitType::class)
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
        if (!$viewData instanceof EventTranslationDto) {
            throw new UnexpectedTypeException($viewData, EventTranslationDto::class);
        }

        $forms = iterator_to_array($forms);

        $forms['title']->setData($viewData->title);
        $forms['location']->setData($viewData->location);
        $forms['description']->setData($viewData->description);
        $forms['locale']->setData($viewData->locale);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var string $title */
        $title = $forms['title']->getData();
        /** @var string $location */
        $location = $forms['location']->getData();
        /** @var string $description */
        $description = $forms['description']->getData();
        /** @var string $locale */
        $locale = $forms['locale']->getData();

        $viewData = new EventTranslationDto($title, $location, $description, $locale);
    }
}
