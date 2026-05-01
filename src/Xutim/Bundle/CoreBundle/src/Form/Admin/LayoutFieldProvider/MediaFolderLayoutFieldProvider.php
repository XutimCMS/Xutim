<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Form\Admin\LayoutFieldProvider;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Config\Layout\Block\Option\MediaFolderBlockItemOption;
use Xutim\MediaBundle\Domain\Model\MediaFolderInterface;
use Xutim\MediaBundle\Repository\MediaFolderRepositoryInterface;

final readonly class MediaFolderLayoutFieldProvider implements LayoutFieldProviderInterface
{
    public function __construct(
        private string $mediaFolderClass,
        private MediaFolderRepositoryInterface $mediaFolderRepository,
    ) {
    }

    public function getOptionClass(): string
    {
        return MediaFolderBlockItemOption::class;
    }

    public function buildField(
        FormBuilderInterface|FormInterface $builder,
        string $fieldName,
        BlockItemOption $option
    ): void {
        $builder->add($fieldName, EntityType::class, [
            'class' => $this->mediaFolderClass,
            'choice_value' => 'id',
            'label' => $this->humanize($fieldName),
            'required' => false,
        ]);
    }

    public function denormalizeForForm(mixed $storedValue): mixed
    {
        if (!is_string($storedValue) || $storedValue === '' || !Uuid::isValid($storedValue)) {
            return null;
        }

        return $this->mediaFolderRepository->findById(Uuid::fromString($storedValue));
    }

    public function normalizeForStorage(mixed $formValue): mixed
    {
        if ($formValue instanceof MediaFolderInterface) {
            return $formValue->id()->toRfc4122();
        }

        return null;
    }

    private function humanize(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }
}
