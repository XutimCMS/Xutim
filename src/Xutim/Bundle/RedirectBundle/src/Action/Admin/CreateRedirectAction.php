<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Entity\User;
use Xutim\RedirectBundle\Domain\Factory\CmsRedirectFactoryInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Form\Admin\RedirectFormData;
use Xutim\RedirectBundle\Form\Admin\RedirectType;

#[Route('/admin/redirect/new', name: 'admin_redirect_new', methods: ['get', 'post'])]
class CreateRedirectAction extends AbstractController
{
    public function __construct(
        private readonly RedirectRepositoryInterface $repo,
        private readonly CmsRedirectFactoryInterface $factory,
        private readonly SiteContext $siteContext
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $locales = $this->siteContext->getLocales();
        $localeChoices = array_combine($locales, $locales);
        $form = $this->createForm(RedirectType::class, null, [
            'action' => $this->generateUrl('admin_redirect_new'),
            'locale_choices' => $localeChoices
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RedirectFormData $data */
            $data = $form->getData();
            $redirect = $this->factory->create(
                $data->getSource(),
                $data->getTargetContentTranslation(),
                $data->getLocale(),
                $data->isPermanent(),
            );

            $this->repo->save($redirect, true);

            $this->addFlash('success', 'flash.changes_made_successfully');

            return $this->redirectToRoute('admin_redirect_list', ['searchTerm' => '']);
        }

        return $this->render('@XutimRedirect/admin/redirect/redirect_new.html.twig', [
            'form' => $form
        ]);
    }
}
