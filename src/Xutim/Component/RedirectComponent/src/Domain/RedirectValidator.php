<?php

declare(strict_types=1);

namespace Xutim\RedirectComponent\Domain;

use Xutim\RedirectComponent\Domain\Exception\CircularRedirectException;
use Xutim\RedirectComponent\Domain\Exception\InvalidRedirectTargetException;
use Xutim\RedirectComponent\Domain\Model\RedirectInterface;

class RedirectValidator
{
    /**
     * @param RedirectInterface[] $redirects
     */
    public function validateRedirects(array $redirects): void
    {
        $map = [];
        foreach ($redirects as $redirect) {
            $source = $redirect->getSource();
            $target = $redirect->getTargetUrl();

            if ($target === null || $target === '') {
                throw new InvalidRedirectTargetException('Target URL must not be empty.');
            }

            if ($source === $target) {
                throw new CircularRedirectException('Redirect from ' . $source . ' to itself detected.');
            }

            if (array_key_exists($target, $map) === true && $map[$target] === $source) {
                throw new CircularRedirectException('Circular redirect loop: ' . $source . ' <-> ' . $target . '.');
            }

            $map[$source] = $target;
        }
    }
}
