<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Traversable;
use Xutim\CoreBundle\Dto\SiteDto;
use Xutim\CoreBundle\Twig\ThemeFinder;

/**
 * @template-extends AbstractType<SiteDto>
 * @template-implements DataMapperInterface<SiteDto>
 */
class SiteType extends AbstractType implements DataMapperInterface
{
    /**
     * @param array<int, string> $locales
     */
    public function __construct(
        private readonly array $locales,
        private readonly ThemeFinder $themeFinder
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $localeChoices = array_combine($this->locales, $this->locales);
        $themes = $this->themeFinder->findAvailableThemes();

        $builder
            ->add('languages', ChoiceType::class, [
                'label' => new TranslatableMessage('content languages', [], 'admin'),
                'choices' => $localeChoices,
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select'
                ]
            ])
            ->add('theme', ChoiceType::class, [
                'label' => new TranslatableMessage('theme', [], 'admin'),
                'choices' => array_combine($themes, $themes),
            ])
            ->add('sender', TextType::class, [
                'label' => new TranslatableMessage('mail sender', [], 'admin'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof SiteDto) {
            throw new UnexpectedTypeException($viewData, SiteDto::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['languages']->setData($viewData->locales);
        $forms['theme']->setData($viewData->theme);
        $forms['sender']->setData($viewData->sender);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var array<string, string> $languages */
        $languages = $forms['languages']->getData();
        /** @var string $theme */
        $theme = $forms['theme']->getData();
        /** @var string $sender */
        $sender = $forms['sender']->getData();

        $viewData = new SiteDto($languages, $theme, $sender);
    }
}
