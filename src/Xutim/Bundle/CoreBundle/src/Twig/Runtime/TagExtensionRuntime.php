<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Domain\Model\TagInterface;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\TagRepository;

class TagExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly RequestStack $requestStack,
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    /**
     * @return array<array{id: string, label: string}>
    */
    public function fetchTags(): array
    {
        $tags = $this->tagRepository->findAll();
        $locale = $this->requestStack->getMainRequest()?->getLocale() ?? 'en';

        return array_map(fn (TagInterface $tag) => [
            'id' => $tag->getId()->toRfc4122(),
            'label' => $tag->getTranslationByLocaleOrAny($locale)->getName(),
        ], $tags);
    }

    public function fetchTag(string $id): ?TagInterface
    {
        return $this->tagRepository->find($id);
    }

    public function getTagLayout(TagInterface $tag): ?string
    {
        return $this->layoutLoader->getTagLayoutTemplate($tag->getLayout());
    }
}
