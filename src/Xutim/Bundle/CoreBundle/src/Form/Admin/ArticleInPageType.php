<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Traversable;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Repository\PageRepository;

/**
 * @template-extends AbstractType<Page>
 * @template-implements DataMapperInterface<Page>
 */
class ArticleInPageType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly PageRepository $pageRepo)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('page', ChoiceType::class, [
                'choices' => array_flip($this->pageRepo->findAllPaths()),
                'label' => new TranslatableMessage('In page', [], 'admin'),
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('submit', [], 'admin')
            ])
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            // Set default locale depending on translator!
            return;
        }

        // invalid data type
        if (!$viewData instanceof Page) {
            throw new UnexpectedTypeException($viewData, Page::class);
        }

        $forms = iterator_to_array($forms);
        $forms['page']->setData($viewData->getId()->toRfc4122());
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var ?string $pageId */
        $pageId = $forms['page']->getData();
        $page = $pageId !== null ? $this->pageRepo->find($pageId) : null;
        if ($page === null) {
            throw new TransformationFailedException(
                sprintf(
                    'The selected page "%s" does not exist.',
                    $pageId
                )
            );
        }

        $viewData = $page;
    }
}
