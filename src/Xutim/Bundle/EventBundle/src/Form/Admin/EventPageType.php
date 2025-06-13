<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Repository\PageRepository;

/**
 * @template-extends AbstractType<array{page: ?string}>
 */
class EventPageType extends AbstractType
{
    public function __construct(private readonly PageRepository $pageRepo)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('page', ChoiceType::class, [
                'choices' => array_flip($this->pageRepo->findAllPaths()),
                'label' => new TranslatableMessage('page', [], 'admin'),
                'required' => false,
            ])
            ->add('submit', SubmitType::class)
        ;
    }
}
