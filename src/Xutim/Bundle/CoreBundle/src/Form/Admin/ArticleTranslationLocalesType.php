<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Traversable;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Dto\Admin\Article\ArticleTranslationLocalesDto;

/**
 * @template-extends AbstractType<ArticleTranslationLocalesDto>
 * @template-implements DataMapperInterface<ArticleTranslationLocalesDto>
 */
class ArticleTranslationLocalesType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sortedMainLocales = $this->siteContext->getMainLocales();
        sort($sortedMainLocales);
        $sortedExtendedLocales = $this->siteContext->getExtendedContentLocales();
        sort($sortedExtendedLocales);

        $choices = [];
        foreach ($sortedMainLocales as $locale) {
            $choices['main languages'][$locale] = $locale;
        }
        foreach ($sortedExtendedLocales as $locale) {
            $choices['extended languages'][$locale] = $locale;
        }

        $builder
            ->add('allTranslationLocales', ChoiceType::class, [
                'label' => new TranslatableMessage('translate into', [], 'admin'),
                'choices' => [
                    'all languages' => true,
                    'specific languages' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('translationLocales', LanguageType::class, [
                'label' => new TranslatableMessage('select languages this content can be translated into', [], 'admin'),
                'multiple' => true,
                'expanded' => true,
                'choices' => $choices,
                'choice_loader' => null,
                'choice_label' => fn (string $locale) => strtoupper($locale),
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin'),
            ])
            ->setDataMapper($this)
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $form = $event->getForm();
                $allTranslationLocales = $form->get('allTranslationLocales')->getData();
                if ($allTranslationLocales !== false) {
                    return;
                }

                /** @var list<string> $existingTranslationLocales */
                $existingTranslationLocales = $form->getConfig()->getOption('existing_translation_locales');
                /** @var list<string> $selectedLocales */
                $selectedLocales = $form->get('translationLocales')->getData();

                $missing = array_diff($existingTranslationLocales, $selectedLocales);
                if ($missing !== []) {
                    $form->get('translationLocales')->addError(
                        new FormError('The following languages already have translations and cannot be removed: ' . implode(', ', array_map('strtoupper', $missing)))
                    );
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('existing_translation_locales', []);
        $resolver->setAllowedTypes('existing_translation_locales', 'array');
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        if (!$viewData instanceof ArticleTranslationLocalesDto) {
            throw new UnexpectedTypeException($viewData, ArticleTranslationLocalesDto::class);
        }

        $forms = iterator_to_array($forms);
        $forms['allTranslationLocales']->setData($viewData->allTranslationLocales);
        $forms['translationLocales']->setData($viewData->translationLocales);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var bool $allTranslationLocales */
        $allTranslationLocales = $forms['allTranslationLocales']->getData();

        /** @var list<string> $translationLocales */
        $translationLocales = $forms['translationLocales']->getData();

        $viewData = new ArticleTranslationLocalesDto($allTranslationLocales, $translationLocales);
    }
}
