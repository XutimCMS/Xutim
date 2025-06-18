<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Context\SiteContext;
use Xutim\CoreBundle\Domain\Model\UserInterface;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;
use Xutim\RedirectBundle\Form\Admin\RedirectFormData;
use Xutim\RedirectBundle\Form\Admin\RedirectType;

#[Route('/admin/redirect/edit/{id}/{locale? }', name: 'admin_redirect_edit', methods: ['get', 'post'])]
class EditRedirectAction extends AbstractController
{
    public function __construct(
        private readonly RedirectRepositoryInterface $repo,
        private readonly SiteContext $siteContext
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $redirect = $this->repo->find($id);
        if ($redirect === null) {
            throw $this->createNotFoundException('The redirect does not exist');
        }
        $this->denyAccessUnlessGranted(UserInterface::ROLE_EDITOR);
        $locales = $this->siteContext->getLocales();
        $localeChoices = array_combine($locales, $locales);
        $form = $this->createForm(RedirectType::class, RedirectFormData::fromRedirect($redirect), [
            'locale_choices' => $localeChoices
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RedirectFormData $data */
            $data = $form->getData();

            $redirect->change(
                $data->getSource(),
                $data->getTargetContentTranslation(),
                $data->getLocale(),
                $data->isPermanent()
            );
            $this->repo->save($redirect, true);

            $this->addFlash('success', 'Changes were made successfully.');

            return $this->redirectToRoute('admin_redirect_edit', ['id' => $redirect->getId()]);
        }

        return $this->render('@XutimRedirect/admin/redirect/redirect_edit.html.twig', [
            'redirect' => $redirect,
            'form' => $form,
        ]);
    }
}
