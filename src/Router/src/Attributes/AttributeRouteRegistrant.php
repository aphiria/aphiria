<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use Aphiria\Routing\Attributes\Controller as ControllerAttribute;
use Aphiria\Routing\Builders\RouteCollectionBuilder;
use Aphiria\Routing\Builders\RouteGroupOptions;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\RouteCollection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Defines the route registrant that registers routes defined via attributes
 */
final class AttributeRouteRegistrant implements IRouteRegistrant
{
    /** @var list<string> The paths to check for controllers */
    private array $paths;
    /** @var ITypeFinder The type finder */
    private ITypeFinder $typeFinder;

    /**
     * @param string|list<string> $paths The path or paths to check for controllers
     * @param ITypeFinder|null $typeFinder The type finder
     */
    public function __construct(string|array $paths, ITypeFinder $typeFinder = null)
    {
        $this->paths = \is_array($paths) ? $paths : [$paths];
        $this->typeFinder = $typeFinder ?? new TypeFinder();
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if any of the classes could not be reflected
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        $routeBuilders = new RouteCollectionBuilder();

        foreach ($this->typeFinder->findAllClasses($this->paths, true) as $controllerClass) {
            $reflectionController = new ReflectionClass($controllerClass);

            // Only allow either Aphiria controllers or classes with the controller attribute
            if (
                !$reflectionController->isSubclassOf(Controller::class)
                && empty($reflectionController->getAttributes(ControllerAttribute::class))
            ) {
                continue;
            }

            $routeGroupOptions = $this->createRouteGroupOptions($reflectionController);

            if ($routeGroupOptions === null) {
                $this->registerRouteBuilders($reflectionController, $routeBuilders);
            } else {
                $routeBuilders->group(
                    $routeGroupOptions,
                    function (RouteCollectionBuilder $routeBuilders) use ($reflectionController) {
                        $this->registerRouteBuilders($reflectionController, $routeBuilders);
                    }
                );
            }
        }

        $routes->addMany($routeBuilders->build()->getAll());
    }

    /**
     * Creates route group options for a controller lass
     *
     * @param ReflectionClass $controller The controller class to create route group options from
     * @return RouteGroupOptions|null The route group options if there were any, otherwise null
     */
    private function createRouteGroupOptions(ReflectionClass $controller): ?RouteGroupOptions
    {
        $routeGroupOptions = null;
        /** @var list<MiddlewareBinding> $middlewareBindings */
        $middlewareBindings = [];
        /** @var list<IRouteConstraint> $routeConstraints */
        $routeConstraints = [];

        foreach ($controller->getAttributes(Middleware::class) as $middlewareAttribute) {
            $middlewareAttributeInstance = $middlewareAttribute->newInstance();
            $middlewareBindings[] = new MiddlewareBinding(
                $middlewareAttributeInstance->className,
                $middlewareAttributeInstance->parameters
            );
        }

        foreach ($controller->getAttributes(RouteConstraint::class) as $routeConstraintAttribute) {
            $routeConstraintAttributeInstance = $routeConstraintAttribute->newInstance();
            $routeConstraintClassName = $routeConstraintAttributeInstance->className;
            /** @psalm-suppress MixedMethodCall We're purposely instantiating the route constraint class */
            $routeConstraints[] = new $routeConstraintClassName(...$routeConstraintAttributeInstance->constructorParameters);
        }

        foreach ($controller->getAttributes(RouteGroup::class) as $routeGroupAttribute) {
            $routeGroupAttributeInstance = $routeGroupAttribute->newInstance();
            /** @psalm-suppress ArgumentTypeCoercion The route constraints will always be a list of route constraints - bug */
            $routeGroupOptions = new RouteGroupOptions(
                $routeGroupAttributeInstance->path,
                $routeGroupAttributeInstance->host,
                $routeGroupAttributeInstance->isHttpsOnly,
                $routeConstraints,
                [], // We'll set the middleware below in the case there was no route group attribute, but there was a middleware attribute
                $routeGroupAttributeInstance->parameters
            );
        }

        // If there was no route group options attributes, but there were constraints or middleware, then create some route group options and add them
        if ($routeGroupOptions === null && (!empty($routeConstraints) || !empty($middlewareBindings))) {
            $routeGroupOptions = new RouteGroupOptions('');
            /** @psalm-suppress PropertyTypeCoercion This will always be a list of route constraints - bug */
            $routeGroupOptions->constraints = [...$routeGroupOptions->constraints, ...$routeConstraints];
            $routeGroupOptions->middlewareBindings = [...$routeGroupOptions->middlewareBindings, ...$middlewareBindings];
        }

        return $routeGroupOptions;
    }

    /**
     * Registers route builders for a controller class
     *
     * @param ReflectionClass $controller The controller class to create route builders from
     * @param RouteCollectionBuilder $routeBuilders The registry to register route builders to
     */
    private function registerRouteBuilders(ReflectionClass $controller, RouteCollectionBuilder $routeBuilders): void
    {
        foreach ($controller->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeBuilder = null;
            /** @var list<MiddlewareBinding> $middlewareBindings */
            $middlewareBindings = [];
            /** @var list<IRouteConstraint> $routeConstraints */
            $routeConstraints = [];

            foreach ($method->getAttributes(Middleware::class) as $middlewareAttribute) {
                $middlewareAttributeInstance = $middlewareAttribute->newInstance();
                $middlewareBindings[] = new MiddlewareBinding(
                    $middlewareAttributeInstance->className,
                    $middlewareAttributeInstance->parameters
                );
            }

            foreach ($method->getAttributes(RouteConstraint::class) as $routeConstraintAttribute) {
                $routeConstraintAttributeInstance = $routeConstraintAttribute->newInstance();
                $routeConstraintClassName = $routeConstraintAttributeInstance->className;
                /** @psalm-suppress MixedMethodCall We're purposely instantiating the route constraint class */
                $routeConstraints[] = new $routeConstraintClassName(...$routeConstraintAttributeInstance->constructorParameters);
            }

            foreach ($method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF) as $routeAttribute) {
                $routeAttributeInstance = $routeAttribute->newInstance();
                $routeBuilder = $routeBuilders->route(
                    $routeAttributeInstance->httpMethods,
                    $routeAttributeInstance->path,
                    $routeAttributeInstance->host,
                    $routeAttributeInstance->isHttpsOnly,
                );
                $routeBuilder->mapsToMethod($controller->getName(), $method->getName());

                if (!empty($middlewareBindings)) {
                    $routeBuilder->withManyMiddleware($middlewareBindings);
                }

                if (!empty($routeConstraints)) {
                    /** @psalm-suppress ArgumentTypeCoercion This is always a list of route constraints - bug */
                    $routeBuilder->withManyConstraints($routeConstraints);
                }

                if (!empty($routeAttributeInstance->name)) {
                    $routeBuilder->withName($routeAttributeInstance->name);
                }

                if (!empty($routeAttributeInstance->parameters)) {
                    $routeBuilder->withManyParameters($routeAttributeInstance->parameters);
                }
            }

            if ($routeBuilder === null) {
                continue;
            }
        }
    }
}
