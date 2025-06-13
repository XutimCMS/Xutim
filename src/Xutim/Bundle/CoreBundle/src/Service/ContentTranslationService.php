<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Xutim\CoreBundle\Domain\Event\Article\ArticleDeletedEvent;
use Xutim\CoreBundle\Domain\Event\ContentTranslation\ContentTranslationDeletedEvent;
use Xutim\CoreBundle\Domain\Event\Page\PageDeletedEvent;
use Xutim\CoreBundle\Domain\Factory\LogEventFactory;
use Xutim\CoreBundle\Domain\Model\ArticleInterface;
use Xutim\CoreBundle\Domain\Model\ContentTranslationInterface;
use Xutim\CoreBundle\Domain\Model\PageInterface;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Exception\LogicException;
use Xutim\CoreBundle\Repository\ArticleRepository;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\MenuItemRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Security\UserStorage;

class ContentTranslationService
{
    public function __construct(
        private readonly LogEventFactory $logEventFactory,
        private readonly UserStorage $userStorage,
        private readonly ContentTranslationRepository $transRepo,
        private readonly ArticleRepository $articleRepo,
        private readonly PageRepository $pageRepo,
        private readonly LogEventRepository $eventRepo,
        private readonly MenuItemRepository $menuItemRepo,
    ) {
    }

    public function deleteTranslation(ContentTranslationInterface $trans): bool
    {
        $object = $trans->getObject();

        // Check if the translation is the last one and delete object too.
        if ($object->getTranslations()->count() === 1) {
            if ($trans->hasPage()) {
                return $this->deletePage($trans->getPage());
            }
            if ($trans->hasArticle()) {
                return $this->deleteArticle($trans->getArticle());
            }
            throw new LogicException('Content translation should have either article or page.');
        } else {
            // Check if the translation is not a translation reference.
            if ($object->getDefaultTranslation()->getId()->equals($trans->getId()) === true) {
                /** @var ContentTranslationInterface $nextTrans */
                foreach ($object->getTranslations() as $nextTrans) {
                    if ($object->getDefaultTranslation()->getId()->equals($nextTrans->getId()) === false) {
                        $object->setDefaultTranslation($nextTrans);
                        break;
                    }
                }
            }
            $object->getTranslations()->removeElement($trans);
        }

        $userIdentifier = $this->userStorage->getUserWithException()->getUserIdentifier();
        $event = new ContentTranslationDeletedEvent($trans->getId());
        $logEntry = $this->logEventFactory->create($trans->getId(), $userIdentifier, ContentTranslation::class, $event);

        $this->transRepo->remove($trans, true);
        $this->eventRepo->save($logEntry, true);

        return true;
    }

    public function deleteArticle(ArticleInterface $article): bool
    {
        $menuItem = $this->menuItemRepo->findOneBy(['article' => $article]);
        if ($menuItem !== null || $article->canBeDeleted() === false) {
            return false;
        }

        $defTrans = $article->getDefaultTranslation();

        $userIdentifier = $this->userStorage->getUserWithException()->getUserIdentifier();
        $logEntryArt = $this->logEventFactory->create($article->getId(), $userIdentifier, Article::class, new ArticleDeletedEvent($article->getId()));
        $logEntryTrans = $this->logEventFactory->create($defTrans->getId(), $userIdentifier, ContentTranslation::class, new ContentTranslationDeletedEvent($defTrans->getId()));

        $article->prepareDeletion();

        foreach ($article->getTranslations() as $trans) {
            $article->getTranslations()->removeElement($trans);
            $this->transRepo->remove($trans, true);
        }

        $this->articleRepo->remove($article, true);

        $this->eventRepo->save($logEntryTrans);
        $this->eventRepo->save($logEntryArt, true);

        return true;
    }

    public function deletePage(PageInterface $page): bool
    {
        $menuItem = $this->menuItemRepo->findOneBy(['page' => $page]);
        if ($menuItem !== null || $page->canBeDeleted() === false) {
            return false;
        }
        $trans = $page->getDefaultTranslation();

        $userIdentifier = $this->userStorage->getUserWithException()->getUserIdentifier();
        $logEntrySec = $this->logEventFactory->create($page->getId(), $userIdentifier, Page::class, new PageDeletedEvent($page->getId()));

        $logEntryTrans = $this->logEventFactory->create($trans->getId(), $userIdentifier, ContentTranslation::class, new ContentTranslationDeletedEvent($trans->getId()));

        $page->prepareDeletion();

        foreach ($page->getTranslations() as $secTrans) {
            $page->getTranslations()->removeElement($secTrans);
            $this->transRepo->remove($secTrans);
        }

        $this->pageRepo->remove($page, true);

        $this->eventRepo->save($logEntryTrans);
        $this->eventRepo->save($logEntrySec, true);

        return true;
    }
}
