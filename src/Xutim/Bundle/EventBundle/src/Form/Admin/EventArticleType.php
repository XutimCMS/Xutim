<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Form\Admin;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Entity\Article;

/**
 * @template-extends AbstractType<array{article: ?Article}>
 */
class EventArticleType extends AbstractType
{
    public function __construct(private readonly string $articleClass)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('article', EntityType::class, [
                'class' => $this->articleClass,
                'label' => new TranslatableMessage('article', [], 'admin'),
                'required' => false,
                'choice_value' => 'id',
                'choice_label' => function (Article $article) {
                    return sprintf(
                        '%s',
                        $article->getTitle()
                    );
                }
            ])
            ->add('submit', SubmitType::class)
        ;
    }
}
