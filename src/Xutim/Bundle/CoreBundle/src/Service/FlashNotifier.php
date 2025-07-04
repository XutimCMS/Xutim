<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class FlashNotifier
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator
    ) {
    }

    public function changesSaved(): void
    {
        $message = $this->translator->trans(
            'flash.changes_made_successfully',
            [],
            'admin'
        );

        $this->flash('success', $message);
    }

    public function stream(string $stream): void
    {
        $this->flash('stream', $stream);
    }

    public function success(string $message): void
    {
        $this->flash('success', $message);
    }

    public function error(string $message): void
    {
        $this->flash('error', $message);
    }

    public function warning(string $message): void
    {
        $this->flash('warning', $message);
    }

    public function flash(string $type, string $message): void
    {
        $session = $this->requestStack->getCurrentRequest()?->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add($type, $message);
        }
    }
}
