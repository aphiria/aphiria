<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Discoverers\Routing;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Application\Discoverers\DiscoveredComponent;
use Aphiria\Application\Discoverers\IComponentBuilder;
use Aphiria\Middleware\Attributes\Middleware as MiddlewareLibraryMiddleware;
use Aphiria\Routing\Attributes\Controller as ControllerAttribute;
use Aphiria\Routing\Attributes\Middleware;
use Aphiria\Routing\Attributes\Route;
use Aphiria\Routing\Attributes\RouteConstraint;
use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteCollectionBuilder;
use Aphiria\Routing\RouteGroupOptions;

/**
 * Defines the route component builder
 *
 * TODO:  Move this into another library
 */
final class RouteComponentBuilder implements IComponentBuilder
{
    /**
     * @param RouteCollection $routes The routes collection to add routes to
     * @param IRouteCache|null $routeCache The route cache to use, or null if not using a cache
     */
    public function __construct(
        private readonly RouteCollection $routes,
        private readonly ?IRouteCache $routeCache = null,
    ) {}

    /**
     * @inheritdoc
     * @param list<DiscoveredComponent> $components
     */
    public function build(array $components): void
    {
        $routeBuilders = new RouteCollectionBuilder();

        foreach ($components as $controllerComponent) {
            // Check if this was a controller (extends Controller or uses the #[Controller] attribute)
            if (!$controllerComponent->class->isSubclassOf(Controller::class) && empty($controllerComponent->class->getAttributes(ControllerAttribute::class))) {
                continue;
            }

            $routeGroupOptions = $this->createRouteGroupOptions($controllerComponent);

            foreach ($controllerComponent->childComponents as $childComponent) {
                // We're going to anchor on route components, then look up their middleware and route constraint siblings
                if (($attribute = $childComponent->attribute?->newInstance()) === null || !($attribute instanceof Route)) {
                    continue;
                }

                if ($routeGroupOptions === null) {
                    $this->registerRouteBuilders($controllerComponent, $routeBuilders);
                } else {
                    $routeBuilders->group(
                        $routeGroupOptions,
                        function (RouteCollectionBuilder $routeBuilders) use ($controllerComponent) {
                            $this->registerRouteBuilders($controllerComponent, $routeBuilders);
                        },
                    );
                }
            }
        }

        $this->routes->addMany($routeBuilders->build()->values);
    }

    /**
     * @inheritdoc
     */
    public function buildFromCache(): void
    {
        if (($routes = $this->routeCache?->get()) instanceof RouteCollection) {
            $this->routes->copy($routes);
        }
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        $this->routeCache?->flush();
    }

    /**
     * @inheritdoc
     */
    public function has(): bool
    {
        return $this->routeCache?->has();
    }

    /**
     * Creates route group options for a controller class
     *
     * @param DiscoveredComponent $controllerComponent The controller component to create route group options from
     * @return RouteGroupOptions|null The route group options if there were any, otherwise null
     */
    private function createRouteGroupOptions(DiscoveredComponent $controllerComponent): ?RouteGroupOptions
    {
        $routeGroupOptions = null;
        /** @var list<MiddlewareBinding> $middlewareBindings */
        $middlewareBindings = [];
        /** @var list<IRouteConstraint> $routeConstraints */
        $routeConstraints = [];

        foreach ($controllerComponent->childComponents as $childComponent) {
            if (
                ($middlewareAttribute = $childComponent->attribute?->newInstance()) instanceof Middleware
                || $middlewareAttribute instanceof MiddlewareLibraryMiddleware
            ) {
                $middlewareBindings[] = new MiddlewareBinding($middlewareAttribute->className, $middlewareAttribute->parameters);
            } elseif (($routeConstraintAttribute = $childComponent->attribute?->newInstance()) instanceof RouteConstraint) {
                $routeConstraints[] = new $routeConstraintAttribute->className(...$routeConstraintAttribute->constructorParameters);
            }
        }

        if (($controllerAttribute = $controllerComponent->attribute?->newInstance()) instanceof ControllerAttribute) {
            $routeGroupOptions = new RouteGroupOptions(
                $controllerAttribute->path,
                $controllerAttribute->host,
                $controllerAttribute->isHttpsOnly,
                $routeConstraints,
                $middlewareBindings,
                $controllerAttribute->parameters,
            );
        }

        // If there was no controller attributes, but there were constraints or middleware, then create some route group options and add them
        if ($routeGroupOptions === null && (!empty($routeConstraints) || !empty($middlewareBindings))) {
            $routeGroupOptions = new RouteGroupOptions('');
            $routeGroupOptions->constraints = [...$routeGroupOptions->constraints, ...$routeConstraints];
            $routeGroupOptions->middlewareBindings = [...$routeGroupOptions->middlewareBindings, ...$middlewareBindings];
        }

        return $routeGroupOptions;
    }

    /**
     * Registers route builders for a controller class
     *
     * @param DiscoveredComponent $routeComponent The route component to create route builders from
     * @param RouteCollectionBuilder $routeBuilders The registry to register route builders to
     */
    private function registerRouteBuilders(DiscoveredComponent $routeComponent, RouteCollectionBuilder $routeBuilders): void
    {
        // For sanity's sake, ensure that the parent component was set to the controller and that the method was set
        \assert($routeComponent->parentComponent instanceof DiscoveredComponent);
        \assert($routeComponent->method instanceof ReflectionMethod);

        // Ensure this is a valid route component
        if (($routeAttribute = $routeComponent->attribute?->newInstance()) === null || !($routeAttribute instanceof Route)) {
            return;
        }

        /** @var list<MiddlewareBinding> $middlewareBindings */
        $middlewareBindings = [];
        /** @var list<IRouteConstraint> $routeConstraints */
        $routeConstraints = [];

        foreach ($routeComponent->siblingComponents as $siblingComponent) {
            if (($middlewareAttribute = $siblingComponent->attribute?->newInstance()) instanceof Middleware || $middlewareAttribute instanceof MiddlewareLibraryMiddleware) {
                $middlewareBindings[] = new MiddlewareBinding($middlewareAttribute->className, $middlewareAttribute->parameters);
            } elseif (($routeConstraintAttribute = $siblingComponent->attribute?->newInstance()) instanceof RouteConstraint) {
                $routeConstraints[] = new $routeConstraintAttribute->className(...$routeConstraintAttribute->constructorParameters);
            }
        }

        $routeBuilder = $routeBuilders->route(
            $routeAttribute->httpMethods,
            $routeAttribute->path,
            $routeAttribute->host,
            $routeAttribute->isHttpsOnly,
        );
        $routeBuilder->mapsToMethod($routeComponent->parentComponent->class->getName(), $routeComponent->method->getName());

        if (!empty($middlewareBindings)) {
            $routeBuilder->withManyMiddleware($middlewareBindings);
        }

        if (!empty($routeConstraints)) {
            $routeBuilder->withManyConstraints($routeConstraints);
        }

        if (!empty((string) $routeAttribute->name)) {
            $routeBuilder->withName((string) $routeAttribute->name);
        }

        if (!empty($routeAttribute->parameters)) {
            $routeBuilder->withManyParameters($routeAttribute->parameters);
        }
    }
}
