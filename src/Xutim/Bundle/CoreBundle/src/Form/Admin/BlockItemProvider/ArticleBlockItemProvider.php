<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\BlockItemProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Xutim\CoreBundle\Config\Layout\Block\Option\ArticleBlockItemOption;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

final readonly class ArticleBlockItemProvider implements BlockItemProviderInterface
{
    public function __construct(private string $articleClass)
    {
    }

    public function getOptionClass(): string
    {
        return ArticleBlockItemOption::class;
    }

    /** @param FormBuilderInterface<mixed> $builder */
    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $builder->add('article', EntityType::class, [
            'class' => $this->articleClass,
            'label' => new TranslatableMessage('article', [], 'admin'),
            'required' => false,
            'choice_value' => 'id',
            'choice_label' => static fn (ArticleInterface $article) => $article->getTitle(),
        ]);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('article', $forms)) {
            return;
        }
        $forms['article']->setData($dto->article);
    }

    /** @param array<string, \Symfony\Component\Form\FormInterface<mixed>> $forms */
    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('article', $forms)) {
            return;
        }
        /** @var ArticleInterface|null $article */
        $article = $forms['article']->getData();
        $dto->article = $article;
    }
}
