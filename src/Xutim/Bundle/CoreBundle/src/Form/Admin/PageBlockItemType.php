<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Traversable;
use Xutim\CoreBundle\Domain\Model\Coordinates;
use Xutim\CoreBundle\Domain\Model\FileInterface;
use Xutim\CoreBundle\Domain\Model\SnippetInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Entity\Color;
use Xutim\CoreBundle\Form\Admin\Dto\PageBlockItemDto;
use Xutim\CoreBundle\Repository\PageRepository;

/**
 * @template-extends AbstractType<PageBlockItemDto>
 * @template-implements DataMapperInterface<PageBlockItemDto>
 */
class PageBlockItemType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly string $fileClass,
        private readonly string $snippetClass,
        private readonly string $tagClass,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('page', ChoiceType::class, [
                'choices' => array_flip($this->pageRepository->findAllPaths()),
                'label' => new TranslatableMessage('page', [], 'admin'),
                'required' => false,
            ])
            ->add('file', EntityType::class, [
                'class' => $this->fileClass,
                'choice_label' => 'id',
                'placeholder' => new TranslatableMessage('select file', [], 'admin'),
                'required' => false,
                'attr' => ['data-controller' => 'media-field'],
                'row_attr' => ['class' => 'd-none'],
            ])
            ->add('snippet', EntityType::class, [
                'label' => 'snippet',
                'class' => $this->snippetClass,
                'required' => false,
            ])
            ->add('tag', EntityType::class, [
                'class' => $this->tagClass,
                'label' => 'tag',
                'required' => false,
            ])
            ->add('link', TextType::class, [
                'label' => 'Link',
                'required' => false,
                'help' => 'Overwrites the page link.',
            ])
            ->add('color', ColorType::class, [
                'label' => new TranslatableMessage('color', [], 'admin'),
                'required' => false,
                'constraints' => [
                    new Length(['max' => 6])
                ],
                'help' => 'Overwrites the page color.',
            ])
            ->add('fileDescription', TextType::class, [
                'label' => 'File description',
                'required' => false,
            ])
            ->add('latitude', NumberType::class, [
                'required' => false,
                'scale' => 6
            ])
            ->add('longitude', NumberType::class, [
                'required' => false,
                'scale' => 6
            ])
            ->add('submit', SubmitType::class)
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if ($viewData === null) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof PageBlockItemDto) {
            throw new UnexpectedTypeException($viewData, PageBlockItemDto::class);
        }

        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['file']->setData($viewData->file);
        $forms['snippet']->setData($viewData->snippet);
        $forms['tag']->setData($viewData->tag);
        $forms['page']->setData($viewData->page->getId());
        $forms['link']->setData($viewData->link);
        $forms['color']->setData($viewData->color);
        $forms['fileDescription']->setData($viewData->fileDescription);
        $forms['latitude']->setData($viewData->coordinates?->latitude);
        $forms['longitude']->setData($viewData->coordinates?->longitude);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        /** @var ?string $pageId */
        $pageId = $forms['page']->getData();
        $page = $pageId !== null ? $this->pageRepository->find($pageId) : null;
        if ($page === null) {
            throw new TransformationFailedException(
                sprintf(
                    'The selected page "%s" does not exist.',
                    $pageId
                )
            );
        }

        /** @var FileInterface|null $file */
        $file = $forms['file']->getData();
        /** @var SnippetInterface $snippet */
        $snippet = $forms['snippet']->getData();
        /** @var TagInterface $tag */
        $tag = $forms['tag']->getData();
        /** @var string|null $link */
        $link = $forms['link']->getData();
        /** @var string|null $colorVal */
        $colorVal = $forms['color']->getData();
        $color = new Color($colorVal);
        /** @var string|null $description */
        $description = $forms['fileDescription']->getData();
        /** @var float|null $latitude */
        $latitude = $forms['latitude']->getData();
        /** @var float|null $longitude */
        $longitude = $forms['longitude']->getData();

        $coords = $latitude !== null && $longitude !== null ? new Coordinates($latitude, $longitude) : null;

        $viewData = new PageBlockItemDto($page, $file, $snippet, $tag, null, $link, $color, $description, $coords);
    }
}
