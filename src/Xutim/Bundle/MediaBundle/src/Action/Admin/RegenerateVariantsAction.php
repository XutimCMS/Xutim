<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Action\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\MediaBundle\Domain\Event\VariantsRegeneratedEvent;
use Xutim\MediaBundle\Domain\Model\MediaVariantInterface;
use Xutim\MediaBundle\Repository\MediaRepositoryInterface;
use Xutim\MediaBundle\Repository\MediaVariantRepositoryInterface;
use Xutim\MediaBundle\Service\VariantCleaner;
use Xutim\MediaBundle\Service\VariantGenerator;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

final class RegenerateVariantsAction
{
    /**
     * @param class-string<MediaVariantInterface> $variantClass
     */
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaVariantRepositoryInterface $variantRepository,
        private readonly VariantGenerator $variantGenerator,
        private readonly VariantCleaner $variantCleaner,
        private readonly EntityManagerInterface $em,
        private readonly LogEventFactory $logEventFactory,
        private readonly LogEventRepository $logEventRepository,
        private readonly UserStorage $userStorage,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly string $variantClass,
        private readonly string $mediaClass,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $media = $this->mediaRepository->findById(Uuid::fromString($id));
        if ($media === null) {
            return new JsonResponse(['error' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->authorizationChecker->isGranted(UserRoles::ROLE_ADMIN)) {
            throw new AccessDeniedException();
        }

        if (!$media->isImage()) {
            return new JsonResponse(['error' => 'Media is not an image'], Response::HTTP_BAD_REQUEST);
        }

        $deleted = $this->variantCleaner->cleanForMedia($media);
        $this->variantRepository->deleteByMedia($media);

        $generatedVariants = $this->variantGenerator->generateAllPresets($media);
        foreach ($generatedVariants as $generated) {
            /** @var MediaVariantInterface $variant */
            $variant = new ($this->variantClass)(
                $media,
                $generated->preset,
                $generated->format,
                $generated->width,
                $generated->height,
                $generated->path,
                $generated->fingerprint,
            );
            $this->variantRepository->save($variant);
        }
        $this->em->flush();

        $presetNames = array_values(array_unique(array_map(
            static fn ($v) => $v->preset,
            $generatedVariants,
        )));
        $event = new VariantsRegeneratedEvent($media->id(), $presetNames, count($generatedVariants));
        $logEntry = $this->logEventFactory->create(
            $media->id(),
            $this->userStorage->getUserWithException()->getUserIdentifier(),
            $this->mediaClass,
            $event,
        );
        $this->logEventRepository->save($logEntry, true);

        return new JsonResponse([
            'success' => true,
            'deletedVariants' => $deleted,
            'generatedVariants' => count($generatedVariants),
        ]);
    }
}
