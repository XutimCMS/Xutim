<?php

declare(strict_types=1);

namespace Xutim\EventBundle\Action\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Xutim\CoreBundle\Entity\Article;
use Xutim\CoreBundle\Entity\User;
use Xutim\EventBundle\Form\Admin\EventArticleType;
use Xutim\EventBundle\Infra\Doctrine\ORM\EventRepository;

#[Route('/event/edit-article/{id}', name: 'admin_event_article_edit', methods: ['get', 'post'])]
class EditEventArticleAction extends AbstractController
{
    public function __construct(private readonly EventRepository $eventRepo)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $event = $this->eventRepo->find($id);
        if ($event === null) {
            throw $this->createNotFoundException('The event does not exist');
        }
        $this->denyAccessUnlessGranted(User::ROLE_EDITOR);
        $form = $this->createForm(EventArticleType::class, ['article' => $event->getArticle()], [
            'action' => $this->generateUrl('admin_event_article_edit', ['id' => $event->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{article: ?Article} $data */
            $data = $form->getData();
            $event->changeArticle($data['article']);
            $this->eventRepo->save($event, true);

            $this->addFlash('success', 'Changes were made successfully.');

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                $stream = $this->renderBlockView('@XutimEvent/admin/event/event_edit_article.html.twig', 'stream_success', [
                    'event' => $event
                ]);
                $this->addFlash('stream', $stream);
            }
            $fallbackUrl = $this->generateUrl('admin_event_edit', ['id' => $event->getId()]);

            return $this->redirect($request->headers->get('referer', $fallbackUrl));
        }

        return $this->render('@XutimEvent/admin/event/event_edit_article.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
}
