<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Layout;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;

/**
 * @template-extends AbstractType<array{layout: ?Layout}>
 */
class PageLayoutType extends AbstractType
{
    public function __construct(private readonly LayoutLoader $layoutLoader)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('layout', ChoiceType::class, [
                'required' => false,
                'choices' => $this->layoutLoader->getPageLayouts(),
                'choice_label' => fn (?Layout $item) => $item->name ?? '',
                'choice_value' => fn (?Layout $item) => $item->code ?? '',
                'choice_attr' => function (?Layout $choice, string $key, string $value) {
                    return [
                        'data-image' => $choice->image ?? ''
                    ];
                },
                'expanded' => false,
                'multiple' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
        ;
    }
}
