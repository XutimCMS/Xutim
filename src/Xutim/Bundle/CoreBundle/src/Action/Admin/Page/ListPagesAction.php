<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\Admin\ContentContext;
use Xutim\CoreBundle\Context\PageTreeContext;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Repository\ContentDraftRepository;
use Xutim\CoreBundle\Repository\LogEventRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\SecurityBundle\Domain\Model\UserInterface;

class ListPagesAction extends AbstractController
{
    public function __construct(
        private readonly PageRepository $pageRepo,
        private readonly ContentContext $contentContext,
        private readonly ContentDraftRepository $draftRepo,
        private readonly LogEventRepository $eventRepo,
        private readonly SiteContext $siteContext,
        private readonly PageTreeContext $pageTreeContext,
    ) {
    }

    public function __invoke(Request $request, ?string $id): Response
    {
        if ($id === null) {
            $page = null;
        } else {
            $page = $this->pageRepo->find($id);
            if ($page === null) {
                throw $this->createNotFoundException('The page does not exist');
            }
        }
        $archived = $request->query->getBoolean('archived');
        $translated = $request->query->getBoolean('translated');
        $locale = $this->contentContext->getLanguage();

        $hierarchy = $this->pageTreeContext->getTree($locale, $archived, $translated);
        $path = $page !== null ? $this->pageRepo->getPathHydrated($page) : [];

        if ($this->isGranted('ROLE_ADMIN') === false && $this->isGranted('ROLE_TRANSLATOR')) {
            /** @var UserInterface $user */
            $user = $this->getUser();
            $locales = $user->getTranslationLocales();
            $totalTranslations = count($locales);
        } else {
            $locales = null;
            $totalTranslations = count($this->siteContext->getLocales());
        }
        if ($page !== null) {
            $translatedPages = $this->pageRepo->countTranslatedTranslations($page, $locales);
        } else {
            $translatedPages = 0;
        }


        $translation = $page === null ? null : $page->getTranslationByLocale($this->contentContext->getLanguage());
        if ($translation === null) {
            $revisionsCount = 0;
            $lastRevision = null;
        } else {
            $revisionsCount = $this->eventRepo->eventsCountPerTranslation($translation);
            $lastRevision = $this->eventRepo->findLastByTranslation($translation);
        }

        $draft = null;
        if ($translation !== null && $translation->isPublished()) {
            $draft = $this->draftRepo->findDraft($translation);
        }

        return $this->render('@XutimCore/admin/page/page_list.html.twig', [
            'hierarchy' => $hierarchy,
            'selectedPage' => $page,
            'path' => $path,
            'translation' => $translation,
            'revisionsCount' => $revisionsCount,
            'lastRevision' => $lastRevision,
            'totalTranslations' => $totalTranslations,
            'translatedTranslations' => $translatedPages,
            'draft' => $draft,
        ]);
    }
}
