<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Attributes;

use Aphiria\Reflection\ITypeFinder;
use Aphiria\Reflection\TypeFinder;
use Aphiria\Routing\Attributes\Middleware;
use Aphiria\Routing\Attributes\Route;
use Aphiria\Routing\Attributes\RouteGroup;
use Aphiria\Routing\Builders\RouteCollectionBuilder;
use Aphiria\Routing\Builders\RouteGroupOptions;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\RouteCollection;
use ReflectionClass;
use ReflectionMethod;

/**
 * Defines the route registrant that registers routes defined via annotations
 */
final class AttributeRouteRegistrant implements IRouteRegistrant
{
    /** @var string[] The paths to check for controllers */
    private array $paths;
    /** @var ITypeFinder The type finder */
    private ITypeFinder $typeFinder;

    /**
     * @param string|string[] $paths The path or paths to check for controllers
     * @param ITypeFinder|null $typeFinder The type finder
     */
    public function __construct(string|array $paths, ITypeFinder $typeFinder = null)
    {
        $this->paths = \is_array($paths) ? $paths : [$paths];
        $this->typeFinder = $typeFinder ?? new TypeFinder();
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        // TODO: Implement registerRoutes() method.
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
        $middlewareBindings = [];

        foreach ($this->annotationReader->getClassAnnotations($controller) as $classAnnotation) {
            if ($classAnnotation instanceof RouteGroup) {
                if ($routeGroupOptions === null) {
                    $routeConstraints = [];

                    foreach ($classAnnotation->constraints as $constraint) {
                        $routeConstraintClassName = $constraint->className;
                        $routeConstraints[] = new $routeConstraintClassName(...$constraint->constructorParams);
                    }

                    $routeGroupOptions = new RouteGroupOptions(
                        $classAnnotation->path,
                        $classAnnotation->host,
                        $classAnnotation->isHttpsOnly,
                        $routeConstraints,
                        [],
                        $classAnnotation->attributes
                    );
                }
            } elseif ($classAnnotation instanceof Middleware) {
                $middlewareBindings[] = new MiddlewareBinding(
                    $classAnnotation->className,
                    $classAnnotation->attributes
                );
            }
        }

        if (!empty($middlewareBindings)) {
            if ($routeGroupOptions === null) {
                $routeGroupOptions = new RouteGroupOptions('');
            }

            $routeGroupOptions->middlewareBindings = [
                ...$routeGroupOptions->middlewareBindings,
                ...$middlewareBindings
            ];
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
            $middlewareBindings = [];

            foreach ($this->annotationReader->getMethodAnnotations($method) as $methodAnnotation) {
                if ($methodAnnotation instanceof Route) {
                    $routeBuilder = $routeBuilders->route(
                        $methodAnnotation->httpMethods,
                        $methodAnnotation->path,
                        $methodAnnotation->host,
                        $methodAnnotation->isHttpsOnly
                    );
                    $routeBuilder->mapsToMethod($controller->getName(), $method->getName());

                    foreach ($methodAnnotation->constraints as $constraint) {
                        $constraintClassName = $constraint->className;
                        $routeBuilder->withConstraint(new $constraintClassName(...$constraint->constructorParams));
                    }

                    if (!empty($methodAnnotation->name)) {
                        $routeBuilder->withName($methodAnnotation->name);
                    }

                    $routeBuilder->withManyAttributes($methodAnnotation->attributes);
                } elseif ($methodAnnotation instanceof Middleware) {
                    $middlewareBindings[] = new MiddlewareBinding(
                        $methodAnnotation->className,
                        $methodAnnotation->attributes
                    );
                }
            }

            if ($routeBuilder === null) {
                continue;
            }

            $routeBuilder->withManyMiddleware($middlewareBindings);
        }
    }
}
