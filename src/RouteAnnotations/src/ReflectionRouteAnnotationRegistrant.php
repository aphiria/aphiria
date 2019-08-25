<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations;

use Aphiria\RouteAnnotations\Annotations\Route;
use Aphiria\RouteAnnotations\Annotations\Middleware;
use Aphiria\RouteAnnotations\Annotations\RouteGroup;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Builders\RouteGroupOptions;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Defines the compiler that takes in annotations and compiles them to valid PHP route definitions
 */
final class ReflectionRouteAnnotationRegistrant implements IRouteAnnotationRegistrant
{
    /** @var string[] The paths to check for controllers */
    private array $paths;
    /** @var IControllerFinder The controller finder */
    private IControllerFinder $controllerFinder;
    /** @var Reader The annotation reader */
    private Reader $annotationReader;

    /**
     * @param string|string[] $paths The path or paths to check for controllers
     * @param IControllerFinder|null $controllerFinder The controller finder
     * @param Reader|null $annotationReader The annotation reader
     */
    public function __construct($paths, IControllerFinder $controllerFinder = null, Reader $annotationReader = null)
    {
        $this->paths = \is_array($paths) ? $paths : [$paths];
        $this->annotationReader = $annotationReader ?? new AnnotationReader();
        $this->controllerFinder = $controllerFinder ?? new FileControllerFinder($this->annotationReader);
    }

    /**
     * @inheritdoc
     * @throws ReflectionException Thrown if a controller class could not be reflected
     */
    public function registerRoutes(RouteBuilderRegistry $routes): void
    {
        foreach ($this->controllerFinder->findAll($this->paths) as $controllerClassName) {
            $controller = new ReflectionClass($controllerClassName);
            $routeGroupOptions = $this->createRouteGroupOptions($controller);

            if ($routeGroupOptions === null) {
                $this->registerRouteBuilders($controller, $routes);
            } else {
                $routes->group($routeGroupOptions, function (RouteBuilderRegistry $routes) use ($controller) {
                    $this->registerRouteBuilders($controller, $routes);
                });
            }
        }
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
                $middlewareBindings[] = new MiddlewareBinding($classAnnotation->className, $classAnnotation->attributes);
            }
        }

        if (!empty($middlewareBindings)) {
            if ($routeGroupOptions === null) {
                $routeGroupOptions = new RouteGroupOptions('');
            }

            $routeGroupOptions->middlewareBindings = [...$routeGroupOptions->middlewareBindings, ...$middlewareBindings];
        }

        return $routeGroupOptions;
    }

    /**
     * Registers route builders for a controller class
     *
     * @param ReflectionClass $controller The controller class to create route builders from
     * @param RouteBuilderRegistry $routes The registry to register route builders to
     */
    private function registerRouteBuilders(ReflectionClass $controller, RouteBuilderRegistry $routes): void
    {
        foreach ($controller->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
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
                    $routeBuilder->toMethod($controller->getName(), $method->getName());

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
