<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Xutim\CoreBundle\Dto\Admin\Article\ArticleDto;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Entity\Page;
use Xutim\CoreBundle\Entity\User;
use Xutim\CoreBundle\Form\Admin\ArticleType;
use Xutim\CoreBundle\Message\Command\Article\CreateArticleCommand;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Security\UserStorage;

#[Route('/article/new/{id?null}', name: 'admin_article_new', methods: ['get', 'post'])]
class CreateArticleAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly ContentTranslationRepository $contentTransRepo
    ) {
    }

    public function __invoke(Request $request, ?Page $page = null): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(ArticleType::class);

        $form->get('content')->setData('[]');
        if ($page !== null) {
            $form->get('page')->setData($page->getId()->toRfc4122());
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ArticleDto $articleDto */
            $articleDto = $form->getData();

            $this->commandBus->dispatch(CreateArticleCommand::fromDto(
                $articleDto,
                $this->userStorage->getUserWithException()->getUserIdentifier()
            ));

            /** @var ContentTranslation $trans */
            $trans = $this->contentTransRepo->findOneBy(['slug' => $articleDto->slug, 'locale' => $articleDto->locale]);
            $article = $trans->getArticle();

            return $this->redirectToRoute('admin_article_show', ['id' => $article->getId()]);
        }

        return $this->render('@XutimCore/admin/article/article_new.html.twig', [
            'form' => $form,
            'selectedPage' => $page
        ]);
    }
}
