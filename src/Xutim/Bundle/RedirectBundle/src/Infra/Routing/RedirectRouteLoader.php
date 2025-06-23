<?php

declare(strict_types=1);

namespace Xutim\RedirectBundle\Infra\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Xutim\RedirectBundle\Action\Admin\RedirectToContentTranslationAction;
use Xutim\RedirectBundle\Domain\Repository\RedirectRepositoryInterface;

final class RedirectRouteLoader extends Loader
{
    private bool $isLoaded = false;

    public function __construct(
        private RedirectRepositoryInterface $repo,
        private string $redirectVersionPath,
        ?string $env = null,
    ) {
        parent::__construct($env);
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if ($this->isLoaded) {
            throw new \RuntimeException('Loader already loaded.');
        }

        $collection = new RouteCollection();
        foreach ($this->repo->findAll() as $redirect) {
            $route = new Route(
                path: '/' . ltrim($redirect->getSource(), '/'),
                defaults: [
                    '_controller' => RedirectToContentTranslationAction::class,
                    'redirect_id' => $redirect->getId()->toRfc4122(),
                    '_locale' => $redirect->getLocale(),
                ],
                options: [
                    'resource' => $this->redirectVersionPath,
                ]
            );

            $hash = substr(hash('sha256', $redirect->getSource() . $redirect->getTargetContentTranslation()->getId()), 0, 12);
            $collection->add('xutim_redirect_' . $hash, $route);
        }

        $this->isLoaded = true;

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'xutim_redirect';
    }
}
