<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Settings;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Dto\SiteDto;
use Xutim\CoreBundle\Form\Admin\SiteType;
use Xutim\CoreBundle\Infra\Layout\LayoutLoader;
use Xutim\CoreBundle\Repository\SiteRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;

class EditSiteSettingsAction extends AbstractController
{
    public function __construct(
        private readonly SiteContext $siteContext,
        private readonly SiteRepository $siteRepository,
        private readonly LayoutLoader $layoutLoader,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_DEVELOPER);
        $site = $this->siteRepository->findDefaultSite();
        $form = $this->createForm(SiteType::class, $site->toDto());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SiteDto $dto */
            $dto = $form->getData();
            $site->change($dto->locales, $dto->extendedContentLocales, $dto->theme, $dto->sender, $dto->referenceLocale);
            $this->siteRepository->save($site, true);
            $this->siteContext->resetDefaultSite();
            $this->siteContext->resetMenu();
            $this->layoutLoader->loadAllLayouts();

            $this->addFlash('success', 'flash.changes_made_successfully');

            return new RedirectResponse($this->router->generate('admin_settings_site'));
        }

        return $this->render('@XutimCore/admin/settings/site.html.twig', [
            'site' => $site,
            'form' => $form
        ]);
    }
}
