<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\ArticleBlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Repository\ArticleRepository;

final readonly class ArticleLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function __construct(
        private string $articleClass,
        private ArticleRepository $articleRepository,
    ) {
    }

    public function getOptionClass(): string
    {
        return ArticleBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $builder->add($fieldName, EntityType::class, [
            'class' => $this->articleClass,
            'label' => new TranslatableMessage('article', [], 'admin'),
            'required' => false,
            'choice_value' => 'id',
            'choice_label' => static fn (ArticleInterface $article) => $article->getTitle(),
            'attr' => ['data-controller' => 'tom-select'],
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_string($storedValue) || $storedValue === '') {
            return null;
        }

        return $this->articleRepository->find($storedValue);
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if ($formValue instanceof ArticleInterface) {
            return $formValue->getId()->toRfc4122();
        }

        return null;
    }
}
