<?php

declare(strict_types=1);

namespace Xutim\SecurityBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Traversable;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Validator\UniqueEmail;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Validator\UniqueUsername;

/**
 * @template-extends AbstractType<CreateUserFormData>
 * @template-implements DataMapperInterface<CreateUserFormData>
 */
class CreateUserType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly SiteContext $siteContext)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->siteContext->getAllLocales();
        $names = array_map(fn (string $locale) => Languages::getName($locale), $locales);
        $localeChoices = array_combine($names, $locales);

        $builder
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', [], 'admin'),
                'constraints' => [
                    new Length(['min' => 3]),
                    new UniqueUsername()
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => new TranslatableMessage('email', [], 'admin'),
                'constraints' => [
                    new Length(['min' => 3]),
                    new UniqueEmail()
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    str_replace('ROLE_', '', UserRoles::ROLE_DEVELOPER) => UserRoles::ROLE_DEVELOPER,
                    str_replace('ROLE_', '', UserRoles::ROLE_ADMIN) => UserRoles::ROLE_ADMIN,
                    str_replace('ROLE_', '', UserRoles::ROLE_TRANSLATOR) => UserRoles::ROLE_TRANSLATOR,
                    str_replace('ROLE_', '', UserRoles::ROLE_EDITOR) => UserRoles::ROLE_EDITOR
                ],
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    return $key . ' (' . match ($value) {
                        UserRoles::ROLE_DEVELOPER => new TranslatableMessage('Has full control over the CMS, including the ability to modify the code.'),
                        UserRoles::ROLE_ADMIN => new TranslatableMessage('Has full control over the CMS, except for code-related operations.'),
                        UserRoles::ROLE_TRANSLATOR => new TranslatableMessage('Can view and translate articles and pages in the assigned languages.'),
                        UserRoles::ROLE_EDITOR => new TranslatableMessage('Can create and edit articles, pages, and other types of content.'),
                        default => ''
                    } . ')';
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => new TranslatableMessage('roles', [], 'admin'),
            ])
            ->add('translationLocales', LanguageType::class, [
                'label' => new TranslatableMessage('languages', [], 'admin'),
                'choices' => $localeChoices,
                'multiple' => true,
                'expanded' => false,
                'constraints' => [
                    new NotNull()
                ],
                'attr' => [
                    'data-controller' => 'tom-select'
                ],
                'choice_loader' => null

            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        // invalid data type
        if ($viewData !== null) {
            throw new UnexpectedTypeException($viewData, 'null');
        }
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var string $email */
        $email = $forms['email']->getData();
        /** @var string $name */
        $name = $forms['name']->getData();
        /** @var list<string> $roles */
        $roles = $forms['roles']->getData();
        /** @var list<string> $locales */
        $locales = $forms['translationLocales']->getData();

        $viewData = new CreateUserFormData(
            $email,
            $name,
            base64_encode(random_bytes(20)),
            $roles,
            $locales,
        );
    }
}
