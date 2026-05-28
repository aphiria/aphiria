<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Middleware\Attributes\ExcludeMiddleware as MiddlewareLibraryExcludeMiddleware;
use Aphiria\Middleware\Attributes\Middleware as MiddlewareLibraryMiddleware;
use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use Aphiria\Routing\Attributes\Controller as ControllerAttribute;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteCollectionBuilder;
use Aphiria\Routing\RouteGroupOptions;
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
    private readonly array $paths;

    /**
     * @param string|list<string> $paths The path or paths to check for controllers
     * @param ITypeFinder $typeFinder The type finder
     */
    public function __construct(string|array $paths, private readonly ITypeFinder $typeFinder = new TypeFinder())
    {
        $this->paths = \is_array($paths) ? $paths : [$paths];
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
                    },
                );
            }
        }

        $routes->addMany($routeBuilders->build()->values);
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
        /** @var list<class-string> $excludedMiddlewareClassNames */
        $excludedMiddlewareClassNames = [];
        $middlewareAttributeClasses = [Middleware::class, MiddlewareLibraryMiddleware::class];
        $excludeMiddlewareAttributeClasses = [ExcludeMiddleware::class, MiddlewareLibraryExcludeMiddleware::class];

        foreach ($middlewareAttributeClasses as $middlewareAttributeClass) {
            foreach ($controller->getAttributes($middlewareAttributeClass, ReflectionAttribute::IS_INSTANCEOF) as $middlewareAttribute) {
                /** @var Middleware|MiddlewareLibraryMiddleware $middlewareAttributeInstance */
                $middlewareAttributeInstance = $middlewareAttribute->newInstance();
                $middlewareBindings[] = new MiddlewareBinding(
                    $middlewareAttributeInstance->className,
                    $middlewareAttributeInstance->parameters,
                );
            }
        }

        foreach ($excludeMiddlewareAttributeClasses as $excludeMiddlewareAttributeClass) {
            foreach ($controller->getAttributes($excludeMiddlewareAttributeClass, ReflectionAttribute::IS_INSTANCEOF) as $excludeMiddlewareAttribute) {
                /** @var ExcludeMiddleware|MiddlewareLibraryExcludeMiddleware $excludeMiddlewareAttributeInstance */
                $excludeMiddlewareAttributeInstance = $excludeMiddlewareAttribute->newInstance();
                $excludedMiddlewareClassNames[] = $excludeMiddlewareAttributeInstance->className;
            }
        }

        foreach ($controller->getAttributes(RouteConstraint::class) as $routeConstraintAttribute) {
            $routeConstraintAttributeInstance = $routeConstraintAttribute->newInstance();
            $routeConstraintClassName = $routeConstraintAttributeInstance->className;
            $routeConstraints[] = new $routeConstraintClassName(...$routeConstraintAttributeInstance->constructorParameters);
        }

        foreach ($controller->getAttributes(ControllerAttribute::class) as $controllerAttribute) {
            $controllerAttributeInstance = $controllerAttribute->newInstance();
            $routeGroupOptions = new RouteGroupOptions(
                $controllerAttributeInstance->path,
                $controllerAttributeInstance->host,
                $controllerAttributeInstance->isHttpsOnly,
                $routeConstraints,
                $middlewareBindings,
                $controllerAttributeInstance->parameters,
                $excludedMiddlewareClassNames,
            );
        }

        // If there was no controller attributes, but there were constraints middleware, then create some route group options and add them
        if ($routeGroupOptions === null && (!empty($routeConstraints) || !empty($middlewareBindings) || !empty($excludedMiddlewareClassNames))) {
            $routeGroupOptions = new RouteGroupOptions(
                '',
                null,
                false,
                $routeConstraints,
                $middlewareBindings,
                [],
                $excludedMiddlewareClassNames,
            );
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
        $excludeMiddlewareAttributeClasses = [ExcludeMiddleware::class, MiddlewareLibraryExcludeMiddleware::class];

        foreach ($controller->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeBuilder = null;
            /** @var list<MiddlewareBinding> $middlewareBindings */
            $middlewareBindings = [];
            /** @var list<IRouteConstraint> $routeConstraints */
            $routeConstraints = [];
            /** @var list<class-string> $excludedMiddlewareClassNames */
            $excludedMiddlewareClassNames = [];
            $middlewareAttributeClasses = [Middleware::class, MiddlewareLibraryMiddleware::class];

            foreach ($middlewareAttributeClasses as $middlewareAttributeClass) {
                foreach ($method->getAttributes($middlewareAttributeClass, ReflectionAttribute::IS_INSTANCEOF) as $middlewareAttribute) {
                    /** @var Middleware|MiddlewareLibraryMiddleware $middlewareAttributeInstance */
                    $middlewareAttributeInstance = $middlewareAttribute->newInstance();
                    $middlewareBindings[] = new MiddlewareBinding(
                        $middlewareAttributeInstance->className,
                        $middlewareAttributeInstance->parameters,
                    );
                }
            }

            foreach ($excludeMiddlewareAttributeClasses as $excludeMiddlewareAttributeClass) {
                foreach ($method->getAttributes($excludeMiddlewareAttributeClass, ReflectionAttribute::IS_INSTANCEOF) as $excludeMiddlewareAttribute) {
                    /** @var ExcludeMiddleware|MiddlewareLibraryExcludeMiddleware $excludeMiddlewareAttributeInstance */
                    $excludeMiddlewareAttributeInstance = $excludeMiddlewareAttribute->newInstance();
                    $excludedMiddlewareClassNames[] = $excludeMiddlewareAttributeInstance->className;
                }
            }

            foreach ($method->getAttributes(RouteConstraint::class) as $routeConstraintAttribute) {
                $routeConstraintAttributeInstance = $routeConstraintAttribute->newInstance();
                $routeConstraintClassName = $routeConstraintAttributeInstance->className;
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

                // Exclude middleware from both group and method-level bindings
                foreach ($excludedMiddlewareClassNames as $excludedMiddlewareClassName) {
                    $routeBuilder->withoutMiddleware($excludedMiddlewareClassName);
                }

                if (!empty($routeConstraints)) {
                    $routeBuilder->withManyConstraints($routeConstraints);
                }

                if (!empty((string) $routeAttributeInstance->name)) {
                    $routeBuilder->withName((string) $routeAttributeInstance->name);
                }

                if (!empty($routeAttributeInstance->parameters)) {
                    $routeBuilder->withManyParameters($routeAttributeInstance->parameters);
                }
            }
        }
    }
}
