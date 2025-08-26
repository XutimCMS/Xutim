<?php

declare(strict_types=1);

namespace Xutim\CoreBundle\Twig\Runtime;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Xutim\CoreBundle\Dto\Admin\FilterDto;
use Xutim\CoreBundle\Routing\AdminUrlGenerator;

class AdminUrlExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly AdminUrlGenerator $router,
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function generatePath(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * @param array<string,mixed> $overrides
     */
    public function getCurrentPathWith(array $overrides = []): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return '#';
        }

        /** @var string $route */
        $route = $request->attributes->getString('_route');
        $routeParams = (array) $request->attributes->get('_route_params', []);

        $queryParams = $request->query->all();

        /** @var array<string, mixed> $params */
        $params = array_merge(
            $routeParams,
            $queryParams,
            $overrides
        );

        return $this->router->generate($route, $params);
    }

    /**
     * @param array<string,mixed> $overrides
     */
    public function getFilterOrderPathWith(
        string $orderField,
        FilterDto $filter,
        array $overrides = []
    ): string {
        $params = array_merge($overrides, [
            'orderColumn' => $orderField,
            'orderDirection' => $filter->getReverseDirection($orderField)
        ]);

        return $this->getCurrentPathWith($params);
    }
}
