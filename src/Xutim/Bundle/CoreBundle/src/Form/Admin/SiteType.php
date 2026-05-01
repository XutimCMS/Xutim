<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Email;
use Traversable;
use Xutim\CoreBundle\Dto\SiteDto;
use Xutim\CoreBundle\Entity\Site;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Twig\ThemeFinder;

/**
 * @template-extends AbstractType<SiteDto>
 * @template-implements DataMapperInterface<SiteDto>
 */
class SiteType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly ThemeFinder $themeFinder,
        private readonly PageRepository $pageRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $themes = $this->themeFinder->findAvailableThemes();
        $pageChoices = array_flip($this->pageRepository->findAllPaths());

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
                'attr' => ['min' => 0, 'placeholder' => (string) Site::DEFAULT_UNTRANSLATED_ARTICLE_AGE_LIMIT_DAYS],
                'help' => new TranslatableMessage('Articles older than this will not appear in the translator dashboard. Set to 0 for no limit.', [], 'admin'),
            ])
            ->add('homepageId', ChoiceType::class, [
                'label' => new TranslatableMessage('homepage', [], 'admin'),
                'choices' => $pageChoices,
                'required' => false,
                'placeholder' => new TranslatableMessage('use default template', [], 'admin'),
                'help' => new TranslatableMessage('Page rendered at the public root. When unset, the theme\'s homepage template is used.', [], 'admin'),
                'attr' => ['data-controller' => 'tom-select'],
            ])
            ->add('adminAlertEmails', TextType::class, [
                'label' => new TranslatableMessage('admin alert emails', [], 'admin'),
                'required' => false,
                'help' => new TranslatableMessage('Email addresses receiving alerts on critical errors (failed jobs, etc.). Type an address and press space, comma or enter.', [], 'admin'),
                'attr' => [
                    'data-controller' => 'tag-input',
                    'data-tag-input-pattern-value' => '^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$',
                    'data-tag-input-max-value' => '20',
                ],
                'constraints' => [
                    new All(['constraints' => [new Email()]]),
                    new Count(max: 20),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this);

        $builder->get('adminAlertEmails')->addModelTransformer(new CallbackTransformer(
            fn (?array $value) => $value === null ? '' : implode(',', $value),
            fn (?string $value) => $value === null || trim($value) === ''
                ? []
                : array_values(array_unique(array_filter(array_map(
                    fn (string $entry) => strtolower(trim($entry)),
                    preg_split('/[\s,]+/', $value) ?: []
                )))),
        ));
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
        $forms['homepageId']->setData($viewData->homepageId);
        $forms['adminAlertEmails']->setData($viewData->adminAlertEmails);
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
        $untranslatedArticleAgeLimitDays = $forms['untranslatedArticleAgeLimitDays']->getData() ?? Site::DEFAULT_UNTRANSLATED_ARTICLE_AGE_LIMIT_DAYS;
        /** @var ?string $homepageId */
        $homepageId = $forms['homepageId']->getData();
        /** @var array<string> $adminAlertEmails */
        $adminAlertEmails = $forms['adminAlertEmails']->getData() ?? [];

        $viewData = new SiteDto(
            $languages,
            $extendedLanguages,
            $theme,
            $sender,
            $referenceLocale,
            $untranslatedArticleAgeLimitDays,
            $homepageId,
            $adminAlertEmails,
        );
    }
}
