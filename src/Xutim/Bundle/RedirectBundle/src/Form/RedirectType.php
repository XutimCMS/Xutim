<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Form\Admin;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Traversable;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;

/**
 * @template-extends AbstractType<RedirectFormData>
 * @template-implements DataMapperInterface<RedirectFormData>
 */
class RedirectType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array{locale_choices: array<string, string>} $localeChoices */
        $localeChoices = $options['locale_choices'];
        $builder
            ->add('source', TextType::class, [
                'label' => new TranslatableMessage('Source url', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new NotNull(),
                ]

            ])
            ->add('targetContentTranslation', EntityType::class, [
                'label' => new TranslatableMessage('Target content translation', [], 'admin'),
                'class' => ContentTranslationInterface::class,
                'required' => true,
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add('locale', ChoiceType::class, [
                'label' => new TranslatableMessage('locale', [], 'admin'),
                'required' => false,
                'choices' => $localeChoices,
                'constraints' => [
                    new Length(['min' => 2]),
                ]
            ])
            ->add('permanent', CheckboxType::class, [
                'label' => new TranslatableMessage('permanent redirect', [], 'admin'),
                'help' => 'Not recommended unless you know why',
            ])
            ->add('submit', SubmitType::class)
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            $forms = iterator_to_array($forms);
            $forms['permanent']->setData(false);
            return;
        }

        if (!$viewData instanceof RedirectFormData) {
            throw new UnexpectedTypeException($viewData, RedirectFormData::class);
        }

        $forms = iterator_to_array($forms);
        $forms['source']->setData($viewData->getSource());
        $forms['targetContentTranslation']->setData($viewData->getTargetContentTranslation());
        $forms['locale']->setData($viewData->getLocale());
        $forms['permanent']->setData($viewData->isPermanent());
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var string $source */
        $source = $forms['source']->getData();
        /** @var ContentTranslationInterface $targetContentTranslation */
        $targetContentTranslation = $forms['targetContentTranslation']->getData();
        /** @var string $locale */
        $locale = $forms['locale']->getData();
        /** @var bool $permanent */
        $permanent = $forms['permanent']->getData();

        $viewData = new RedirectFormData($source, $targetContentTranslation, $locale, $permanent);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'locale_choices' => [],
        ]);

        $resolver->setAllowedTypes('locale_choices', ['array']);
    }
}
