<?php

declare(strict_types=1);

namespace Xutim\SnippetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Traversable;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\SecurityBundle\Service\TranslatorAuthChecker;
use Xutim\SnippetBundle\Domain\Model\SnippetCategory;

/**
 * @template-extends AbstractType<SnippetFormData>
 * @template-implements DataMapperInterface<SnippetFormData>
 */
class SnippetType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly SiteContext $context,
        private readonly TranslatorAuthChecker $authChecker
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->context->getLocales();
        $builder
            ->add('code', TextType::class, [
                'label' => new TranslatableMessage('code', [], 'admin'),
                'required' => true,
                'disabled' => $options['can_edit'] === false,
                'constraints' => [
                    new Length(['min' => 3]),
                    new NotNull(),
                    new Regex([
                        'pattern' => '/^[a-z0-9]+(-[a-z0-9]+)*$/',
                        'message' => 'The code should be written in kebab-case.'
                    ])
                ],
                'help' => 'The code will be used in twig files directly and it should be in kebab-case e.g. main-menu',
            ])
            ->add('description', TextareaType::class, [
                'label' => new TranslatableMessage('description', [], 'admin'),
                'required' => false,
                'disabled' => $options['can_edit'] === false,
            ])
            ->add('category', EnumType::class, [
                'label' => new TranslatableMessage('category', [], 'admin'),
                'required' => true,
                'disabled' => $options['can_edit'] === false,
                'class' => SnippetCategory::class,
            ])
        ;
        foreach ($locales as $locale) {
            $builder
                ->add($locale, TextareaType::class, [
                    'required' => false,
                    'label' => strtoupper($locale),
                    'disabled' => $this->authChecker->canTranslate($locale) ? false : true
                ]);
        }
    
        $builder
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin'),
            ])
            ->setDataMapper($this)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'can_edit' => true
        ]);
        $resolver->addAllowedTypes('can_edit', 'bool');
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof SnippetFormData) {
            throw new UnexpectedTypeException($viewData, SnippetFormData::class);
        }

        $forms = iterator_to_array($forms);
        $forms['code']->setData($viewData->getCode());
        $forms['description']->setData($viewData->getDescription());
        $forms['category']->setData($viewData->getCategory());

        foreach ($viewData->getContents() as $locale => $content) {
            if (array_key_exists($locale, $forms) === true) {
                $forms[$locale]->setData($content);
            }
        }
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var string $code */
        $code = $forms['code']->getData();

        /** @var string $description */
        $description = $forms['description']->getData() ?? '';

        /** @var SnippetCategory $category */
        $category = $forms['category']->getData();

        $contents = [];
        foreach ($this->context->getLocales() as $locale) {
            /** @var string|null $content */
            $content = $forms[$locale]->getData();
            $contents[$locale] = $content ?? '';
        }

        $viewData = new SnippetFormData($code, $description, $category, $contents);
    }
}
