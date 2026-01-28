<?php

declare(strict_types=1);

namespace Xutim\EditorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xutim\EditorBundle\Domain\Template\BlockTemplateInterface;
use Xutim\EditorBundle\Domain\Template\BlockTemplateRegistry;
use Xutim\EditorBundle\Entity\Block\LayoutBlock;

/**
 * @extends AbstractType<LayoutBlock>
 */
final class LayoutBlockType extends AbstractType
{
    public function __construct(
        private readonly BlockTemplateRegistry $templateRegistry,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $templates = $this->templateRegistry->all();
        $choices = [];
        foreach ($templates as $template) {
            $choices[$template->getLabel()] = $template->getName();
        }

        $builder->add('template', ChoiceType::class, [
            'choices' => $choices,
            'choice_attr' => function (string $value) use ($templates): array {
                $template = $templates[$value] ?? null;
                if ($template === null) {
                    return [];
                }

                return [
                    'data-slots' => count($template->getSlots()),
                    'data-description' => $template->getDescription(),
                ];
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LayoutBlock::class,
        ]);
    }
}
