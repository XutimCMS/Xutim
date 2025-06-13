<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\MessageHandler\Command\Tag;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xutim\CoreBundle\Domain\Event\Tag\TagUpdatedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Factory\TagTranslationFactory;
use Xutim\CoreBundle\Entity\Tag;
use Xutim\CoreBundle\Message\Command\Tag\EditTagCommand;
use Xutim\CoreBundle\MessageHandler\CommandHandlerInterface;
use Xutim\CoreBundle\Repository\FileRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\TagRepository;
use Xutim\CoreBundle\Repository\TagTranslationRepository;
use Xutim\CoreBundle\Service\SearchContentBuilder;

readonly class EditTagHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private TagRepository $tagRepo,
        private TagTranslationRepository $tagTransRepo,
        private LogEventRepository $eventRepository,
        private FileRepository $fileRepository,
        private SearchContentBuilder $searchContentBuilder,
        private TagTranslationFactory $tagTranslationFactory
    ) {
    }

    public function __invoke(EditTagCommand $cmd): void
    {
        $tag = $this->tagRepo->find($cmd->tagId);
        if ($tag === null) {
            throw new NotFoundHttpException(sprintf(
                'Tag "%s" could not be found',
                $cmd->tagId
            ));
        }

        $trans = $tag->getTranslationByLocale($cmd->locale);
        $file = null;
        if ($cmd->hasFeaturedImage() === true) {
            $file = $this->fileRepository->find($cmd->featuredImageId);
        }

        $tag->change($cmd->color, $file);
        $tag->changeLayout($cmd->layout);
        if ($trans === null) {
            $trans = $this->tagTranslationFactory->create($cmd->name, $cmd->slug, $cmd->locale, $tag);
            $tag->addTranslation($trans);
        } else {
            $trans->change($cmd->name, $cmd->slug);
        }

        foreach ($tag->getArticles() as $article) {
            $articleTrans = $article->getTranslationByLocale($trans->getLocale());
            if ($articleTrans !== null) {
                $searchTagContent = $this->searchContentBuilder->buildTagContent($articleTrans);
                $articleTrans->changeSearchTagContent($searchTagContent);
            }
        }

        $this->tagRepo->save($tag);
        $this->tagTransRepo->save($trans, true);

        $event = new TagUpdatedEvent(
            $tag->getId(),
            $trans->getId(),
            $cmd->name,
            $cmd->slug,
            $cmd->locale,
            $cmd->color->getValueOrDefaultHex(),
            $tag->getFeaturedImage()?->getId()
        );
        $logEntry = $this->logEventFactory->create($tag->getId(), $cmd->userIdentifier, Tag::class, $event);
        $this->eventRepository->save($logEntry, true);
    }
}
