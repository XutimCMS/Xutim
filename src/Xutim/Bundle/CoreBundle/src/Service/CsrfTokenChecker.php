<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @author Tomas Jakl <tomasjakll@gmail.com>
 */
readonly class CsrfTokenChecker
{
    public function __construct(private CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    /**
     * Checks the validity of a CSRF token.
     */
    private function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($id, $token));
    }

    /**
     * Check csrf token manually. If it is not valid throw an exception.
     */
    public function checkTokenFromRequest(string $intention, Request $request): void
    {
        $token = $request->headers->get('X-CSRF-Token');

        if ($this->isCsrfTokenValid($intention, $token) === false) {
            throw new AccessDeniedHttpException('CSRF token invalid.');
        }
    }

    public function checkTokenFromFormRequest(string $intention, Request $request): void
    {
        $attrs = $request->request->all();
        if (array_key_exists('form', $attrs) === false
            || is_array($attrs['form']) === false
            || array_key_exists('_token', $attrs['form']) === false
            || is_string($attrs['form']['_token']) === false
        ) {
            throw new AccessDeniedHttpException('CSRF token invalid.');
        }
        $token = $attrs['form']['_token'];

        if ($this->isCsrfTokenValid($intention, $token) === false) {
            throw new AccessDeniedHttpException('CSRF token invalid.');
        }
    }
}
