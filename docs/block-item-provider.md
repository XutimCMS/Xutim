# Block Item Provider

A block item provider adds custom form fields to the block item admin form. Each provider is paired with a `BlockItemOption` that defines what data the block item must have to fulfill a layout requirement.

## How It Works

1. A **BlockItemOption** declares a layout requirement (e.g. "this block item needs an embed URL")
2. A **BlockItemProvider** adds the form fields, and maps data between the form and the `BlockItemDto`
3. Data flows through `BlockItemDto::$extra` — a generic `array<string, mixed>` stored as JSON in the database
4. Layout configs reference options to define what a block expects
5. Providers are auto-tagged via `autoconfigure`

## Creating a Provider

### 1. Option Class

Implements `BlockItemOption`. Decides if a block item fulfills the requirement.

```php
<?php

declare(strict_types=1);

namespace App\Config\Block\Option;

use Xutim\CoreBundle\Config\Layout\Block\Option\BlockItemOption;
use Xutim\CoreBundle\Domain\Model\BlockItemInterface;

readonly class EmbedUrlBlockItemOption implements BlockItemOption
{
    public function canFullFill(BlockItemInterface $item): bool
    {
        $embedUrl = $item->getExtra()['embedUrl'] ?? null;

        return $embedUrl !== null && $embedUrl !== '';
    }

    public function getName(): string
    {
        return 'embed url item';
    }
}
```

### 2. Provider Class

Implements `BlockItemProviderInterface`. Adds form fields and handles data mapping via `$dto->extra`.

```php
<?php

declare(strict_types=1);

namespace App\Form\BlockItemProvider;

use App\Config\Block\Option\EmbedUrlBlockItemOption;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Xutim\CoreBundle\Form\Admin\BlockItemProvider\BlockItemProviderInterface;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

final readonly class EmbedUrlBlockItemProvider implements BlockItemProviderInterface
{
    public function getOptionClass(): string
    {
        return EmbedUrlBlockItemOption::class;
    }

    public function buildFormFields(FormBuilderInterface $builder): void
    {
        $builder->add('embedUrl', TextType::class, [
            'label' => 'Embed URL',
            'required' => false,
        ]);
    }

    public function mapDataToForms(BlockItemDto $dto, array $forms): void
    {
        if (!array_key_exists('embedUrl', $forms)) {
            return;
        }
        $forms['embedUrl']->setData($dto->extra['embedUrl'] ?? null);
    }

    public function mapFormsToData(array $forms, BlockItemDto $dto): void
    {
        if (!array_key_exists('embedUrl', $forms)) {
            return;
        }
        $dto->extra['embedUrl'] = $forms['embedUrl']->getData();
    }
}
```

### 3. Layout Config

Reference the option in a layout config file:

```php
// templates/themes/default/layout/block/embed/config.php

use App\Config\Block\Option\EmbedUrlBlockItemOption;
use Xutim\CoreBundle\Config\Layout\LayoutConfig;

return new LayoutConfig(
    code: 'embed',
    name: 'Embed Block',
    config: [new EmbedUrlBlockItemOption()],
);
```

When a block uses the `embed` layout, the add/edit item form will show the `embedUrl` field.

## Storing Data in Real Database Columns

By default, provider data is stored in the `extra` JSON column. If you need dedicated database columns (for querying, indexing, or type safety), extend the `BlockItem` entity:

```php
namespace App\Entity\Core;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Xutim\CoreBundle\Entity\BlockItem as XutimBlockItem;
use Xutim\CoreBundle\Form\Admin\Dto\BlockItemDto;

#[Entity]
#[Table(name: 'xutim_block_item')]
class BlockItem extends XutimBlockItem
{
    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $embedUrl = null;

    public function change(/* ... */ array $extra = []): void
    {
        // Extract your field from $extra before passing to parent
        $this->embedUrl = $extra['embedUrl'] ?? null;
        unset($extra['embedUrl']);

        parent::change(/* ... */ $extra);
    }

    public function getDto(): BlockItemDto
    {
        $dto = parent::getDto();
        $dto->extra['embedUrl'] = $this->embedUrl;

        return $dto;
    }
}
```

The provider still reads/writes `$dto->extra['embedUrl']` — the entity bridges between `$extra` and the real column transparently.
