<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Dto\Admin\Page\PageDto;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\PageType;
use Xutim\CoreBundle\Message\Command\Page\CreatePageCommand;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Repository\PageRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/page/new/{id?}', name: 'admin_page_new', methods: ['get', 'post'])]
class CreatePageAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly ContentTranslationRepository $transRepo,
        private readonly PageRepository $pageRepo
    ) {
    }

    public function __invoke(Request $request, ?string $id = null): Response
    {
        if ($id === null) {
            $page = null;
        } else {
            $page = $this->pageRepo->find($id);
            if ($page === null) {
                throw $this->createNotFoundException('The page does not exist');
            }
        }
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(PageType::class);
        $form->get('content')->setData('[]');
        if ($page !== null) {
            $form->get('parent')->setData($page->getId()->toRfc4122());
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PageDto $pageDto */
            $pageDto = $form->getData();

            $this->commandBus->dispatch(CreatePageCommand::fromDto(
                $pageDto,
                $this->userStorage->getUserWithException()->getUserIdentifier()
            ));

            /** @var ContentTranslation $trans */
            $trans = $this->transRepo->findOneBy(['slug' => $pageDto->slug, 'locale' => $pageDto->locale]);

            return $this->redirectToRoute('admin_page_list', ['id' => $trans->getPage()->getId()]);
        }

        return $this->render('@XutimCore/admin/page/page_new.html.twig', [
            'form' => $form
        ]);
    }
}
