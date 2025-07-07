<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Traversable;
use Xutim\RedirectBundle\Infra\Validator\ValidRedirect;

/**
 * @template-extends AbstractType<RedirectFormData>
 * @template-implements DataMapperInterface<RedirectFormData>
 */
class RedirectType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [
                    new NotNull(),
                ],
                'disabled' => true,
                'attr' => [
                    'hidden' => true
                ]
            ])
            ->add('source', TextType::class, [
                'label' => new TranslatableMessage('Source url', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new NotNull(),
                    new Regex([
                        'pattern' => '~^/[\w\-/.#]*$~',
                        'message' => 'The path must start with a "/" and may contain letters, numbers, dashes, underscores, slashes, dots, and hashes.',
                    ]),
                ]
            ])
            ->add('target', TextType::class, [
                'label' => new TranslatableMessage('Target url', [], 'admin'),
                'required' => true,
                'constraints' => [
                    new NotNull(),
                    new Regex([
                        'pattern' => '~^/[\w\-/.#]*$~',
                        'message' => 'The path must start with a "/" and may contain letters, numbers, dashes, underscores, slashes, dots, and hashes.',
                    ]),
                ],
            ])
            ->add('permanent', CheckboxType::class, [
                'label' => new TranslatableMessage('permanent redirect', [], 'admin'),
                'help' => 'Not recommended unless you know why',
                'required' => false,
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
        $forms['target']->setData($viewData->getTarget());
        $forms['permanent']->setData($viewData->isPermanent());
        $forms['id']->setData($viewData->getId());
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var string $source */
        $source = $forms['source']->getData();
        /** @var string $target */
        $target = $forms['target']->getData();
        /** @var bool $permanent */
        $permanent = $forms['permanent']->getData();
        /** @var ?Uuid $id */
        $id = $forms['id']->getData();

        $viewData = new RedirectFormData($source, $target, $permanent, $id);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [new ValidRedirect()],
        ]);
    }
}
