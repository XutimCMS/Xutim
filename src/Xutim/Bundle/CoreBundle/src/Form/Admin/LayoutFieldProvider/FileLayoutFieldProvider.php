<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\FileBlockItemOption;
use Xutim\MediaBundle\Domain\Model\MediaInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;

final readonly class FileLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function __construct(
        private string $mediaClass,
        private MediaRepositoryInterface $mediaRepository,
    ) {
    }

    public function getOptionClass(): string
    {
        return FileBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $builder->add($fieldName, EntityType::class, [
            'class' => $this->mediaClass,
            'choice_label' => 'id',
            'choice_value' => 'id',
            'label' => $this->humanize($fieldName),
            'placeholder' => new TranslatableMessage('select file', [], 'admin'),
            'required' => false,
            'attr' => ['data-controller' => 'media-field'],
            'row_attr' => ['class' => 'd-none'],
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_string($storedValue) || $storedValue === '' || !Uuid::isValid($storedValue)) {
            return null;
        }

        return $this->mediaRepository->findById(Uuid::fromString($storedValue));
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if ($formValue instanceof MediaInterface) {
            return $formValue->id()->toRfc4122();
        }

        return null;
    }

    private function humanize(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
