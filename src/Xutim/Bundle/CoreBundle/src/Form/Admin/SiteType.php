<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
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
    public function __construct(private readonly ThemeFinder $themeFinder)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $themes = $this->themeFinder->findAvailableThemes();

        $builder
            ->add('languages', LocaleType::class, [
                'label' => new TranslatableMessage('content languages', [], 'admin'),
                'multiple' => true,
                'attr' => [
                    'data-controller' => 'tom-select'
                ]
            ])
            ->add('extendedLanguages', LocaleType::class, [
                'label' => new TranslatableMessage('extended content languages', [], 'admin'),
                'required' => false,
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
            ->add('referenceLocale', LocaleType::class, [
                'label' => new TranslatableMessage('reference language', [], 'admin'),
                'required' => false,
                'placeholder' => 'Select reference language',
            ])
            ->add('untranslatedArticleAgeLimitDays', IntegerType::class, [
                'label' => new TranslatableMessage('untranslated article age limit (days)', [], 'admin'),
                'required' => true,
                'attr' => ['min' => 0, 'placeholder' => '180'],
                'help' => new TranslatableMessage('Articles older than this will not appear in the translator dashboard. Set to 0 for no limit.', [], 'admin'),
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
        $forms['extendedLanguages']->setData($viewData->extendedContentLocales);
        $forms['theme']->setData($viewData->theme);
        $forms['sender']->setData($viewData->sender);
        $forms['referenceLocale']->setData($viewData->referenceLocale);
        $forms['untranslatedArticleAgeLimitDays']->setData($viewData->untranslatedArticleAgeLimitDays);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var array<string, string> $languages */
        $languages = $forms['languages']->getData();
        /** @var array<string, string> $extendedLanguages */
        $extendedLanguages = $forms['extendedLanguages']->getData();
        /** @var string $theme */
        $theme = $forms['theme']->getData();
        /** @var string $sender */
        $sender = $forms['sender']->getData();
        /** @var string $referenceLocale */
        $referenceLocale = $forms['referenceLocale']->getData() ?? 'en';

        /** @var int $untranslatedArticleAgeLimitDays */
        $untranslatedArticleAgeLimitDays = $forms['untranslatedArticleAgeLimitDays']->getData() ?? 180;

        $viewData = new SiteDto($languages, $extendedLanguages, $theme, $sender, $referenceLocale, $untranslatedArticleAgeLimitDays);
    }
}
