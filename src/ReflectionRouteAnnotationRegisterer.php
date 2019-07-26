<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/route-annotations/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations;

use Aphiria\RouteAnnotations\Annotations\Route;
use Aphiria\RouteAnnotations\Annotations\RouteConstraint;
use Aphiria\RouteAnnotations\Annotations\Middleware;
use Aphiria\RouteAnnotations\Annotations\RouteGroup;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Builders\RouteGroupOptions;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Doctrine\Annotations\Reader;
use ReflectionClass;
use ReflectionMethod;

/**
 * Defines the compiler that takes in annotations and compiles them to valid PHP route definitions
 */
final class ReflectionRouteAnnotationRegisterer implements IRouteAnnotationRegisterer
{
    /** @var RouteBuilderRegistry The route builders to register routes to */
    private RouteBuilderRegistry $routes;
    /** @var Reader The annotation reader */
    private Reader $annotationReader;

    /**
     * @param RouteBuilderRegistry $routes The route builders to register routes to
     * @param Reader $annotationReader The annotation reader
     */
    public function __construct(RouteBuilderRegistry $routes, Reader $annotationReader)
    {
        $this->routes = $routes;
        $this->annotationReader = $annotationReader;
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(string $className): void
    {
        $class = new ReflectionClass($className);
        $routeGroupOptions = $this->createRouteGroupOptions($class);

        if ($routeGroupOptions === null) {
            $this->registerRouteBuilders($class, $this->routes);
        } else {
            $this->routes->group($routeGroupOptions, function (RouteBuilderRegistry $routes) use ($class) {
                $this->registerRouteBuilders($class, $routes);
            });
        }
    }

    /**
     * Creates route group options for a controller lass
     *
     * @param ReflectionClass $class The class to create route group options from
     * @return RouteGroupOptions|null The route group options if there were any, otherwise null
     */
    private function createRouteGroupOptions(ReflectionClass $class): ?RouteGroupOptions
    {
        $routeGroupOptions = null;
        $middlewareBindings = [];
        $routeConstraints = [];

        foreach ($this->annotationReader->getClassAnnotations($class) as $classAnnotation) {
            if ($classAnnotation instanceof RouteGroup) {
                if ($routeGroupOptions === null) {
                    $routeGroupOptions = new RouteGroupOptions(
                        $classAnnotation->path,
                        $classAnnotation->host,
                        $classAnnotation->isHttpsOnly,
                        [],
                        [],
                        $classAnnotation->attributes
                    );
                }
            } elseif ($classAnnotation instanceof Middleware) {
                $middlewareBindings[] = new MiddlewareBinding($classAnnotation->className, $classAnnotation->attributes);
            } elseif ($classAnnotation instanceof RouteConstraint) {
                $routeConstraintClassName = $classAnnotation->className;
                $routeConstraints[] = new $routeConstraintClassName(...$classAnnotation->constructorParams);
            }
        }

        if (!empty($middlewareBindings)) {
            if ($routeGroupOptions === null) {
                $routeGroupOptions = new RouteGroupOptions('');
            }

            $routeGroupOptions->middlewareBindings = [...$routeGroupOptions->middlewareBindings, ...$middlewareBindings];
        }

        if (!empty($routeConstraints)) {
            if ($routeGroupOptions === null) {
                $routeGroupOptions = new RouteGroupOptions('');
            }

            $routeGroupOptions->constraints = [...$routeGroupOptions->constraints, ...$routeConstraints];
        }

        return $routeGroupOptions;
    }

    /**
     * Registers route builders for a controller class
     *
     * @param ReflectionClass $class The class to create route builders from
     * @param RouteBuilderRegistry $routes The registry to register route builders to
     */
    private function registerRouteBuilders(ReflectionClass $class, RouteBuilderRegistry $routes): void
    {
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeBuilder = null;
            $middlewareBindings = [];

            foreach ($this->annotationReader->getMethodAnnotations($method) as $methodAnnotation) {
                if ($methodAnnotation instanceof Route) {
                    $routeBuilder = $routes->map(
                        $methodAnnotation->httpMethods,
                        $methodAnnotation->path,
                        $methodAnnotation->host,
                        $methodAnnotation->isHttpsOnly
                    );

                    foreach ($methodAnnotation->constraints as $constraint) {
                        $constraintClassName = $constraint->className;
                        $routeBuilder->withConstraint(new $constraintClassName(...$constraint->constructorParams));
                    }

                    if (!empty($methodAnnotation->name)) {
                        $routeBuilder->withName($methodAnnotation->name);
                    }

                    $routeBuilder->withManyAttributes($methodAnnotation->attributes);
                } elseif ($methodAnnotation instanceof Middleware) {
                    $middlewareBindings[] = new MiddlewareBinding($methodAnnotation->className, $methodAnnotation->attributes);
                }
            }

            if ($routeBuilder === null) {
                continue;
            }

            $routeBuilder->withManyMiddleware($middlewareBindings);
        }
    }
}
