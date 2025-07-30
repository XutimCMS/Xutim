<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Action\Admin\Article;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Xutim\CoreBundle\Entity\ContentTranslation;
use Xutim\CoreBundle\Form\Admin\ArticleType;
use Xutim\CoreBundle\Form\Admin\Dto\CreateArticleFormData;
use Xutim\CoreBundle\Message\Command\Article\CreateArticleCommand;
use Xutim\CoreBundle\Repository\ContentTranslationRepository;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;
use Xutim\SecurityBundle\Security\UserRoles;
use Xutim\SecurityBundle\Service\UserStorage;

class CreateArticleAction extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserStorage $userStorage,
        private readonly ContentTranslationRepository $contentTransRepo,
        private readonly AdminUrlGenerator $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserRoles::ROLE_EDITOR);
        $form = $this->createForm(ArticleType::class);

        $form->get('content')->setData('[]');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CreateArticleFormData $data */
            $data = $form->getData();

            $this->commandBus->dispatch(CreateArticleCommand::fromFormData(
                $data,
                $this->userStorage->getUserWithException()->getUserIdentifier()
            ));

            /** @var ContentTranslation $trans */
            $trans = $this->contentTransRepo->findOneBy(['slug' => $data->getSlug(), 'locale' => $data->getLocale()]);
            $article = $trans->getArticle();

            return new RedirectResponse($this->router->generate('admin_article_show', ['id' => $article->getId()]));
        }

        return $this->render('@XutimCore/admin/article/article_new.html.twig', [
            'form' => $form
        ]);
    }
}
