<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Settings;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Dto\SiteDto;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\SiteType;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\SiteRepository;

#[Route('/settings/site', name: 'admin_settings_site', methods: ['get', 'post'])]
class EditSiteSettingsAction extends AbstractController
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly SiteRepository $siteRepository,
        private readonly LayoutLoader $layoutLoader
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_DEVELOPER);
        $site = $this->siteRepository->findDefaultSite();
        $form = $this->createForm(SiteType::class, $site->toDto());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SiteDto $dto */
            $dto = $form->getData();
            $site->change($dto->locales, $dto->extendedContentLocales, $dto->theme, $dto->sender);
            $this->siteRepository->save($site, true);
            $this->siteContext->resetDefaultSite();
            $this->layoutLoader->loadAllLayouts();

            $this->addFlash('success', 'flash.changes_made_successfully');

            return $this->redirectToRoute('admin_settings_site');
        }

        return $this->render('@XutimCore/admin/settings/site.html.twig', [
            'site' => $site,
            'form' => $form
        ]);
    }
}
