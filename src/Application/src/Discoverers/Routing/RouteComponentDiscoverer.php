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
use Aphiria\Application\Discoverers\IComponentDiscoverer;
use Aphiria\Middleware\Attributes\Middleware as MiddlewareLibraryMiddleware;
use Aphiria\Routing\Attributes\Controller as ControllerAttribute;
use Aphiria\Routing\Attributes\Middleware;
use Aphiria\Routing\Attributes\Route;
use Aphiria\Routing\Attributes\RouteConstraint;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

/**
 * Defines the route component discoverer
 *
 * TODO:  Move this into another library
 */
final class RouteComponentDiscoverer implements IComponentDiscoverer
{
    public string $componentName {
        get => 'aphiria:routing';
    }

    /**
     * @inheritdoc
     */
    public function discover(ReflectionClass $class): array
    {
        $components = [];
        /** @var DiscoveredComponent|null $controllerComponent */
        $controllerComponent = null;

        // Only consider classes that extend Controller or use the #[Controller] attribute
        if (!empty($class->getAttributes(ControllerAttribute::class))) {
            $controllerComponent = new DiscoveredComponent($this->componentName, $class, attribute: $class->getAttributes(ControllerAttribute::class)[0]);
        } elseif ($class->isSubclassOf(Controller::class)) {
            $controllerComponent = new DiscoveredComponent($this->componentName, $class);
        } else {
            return [];
        }

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeComponents = [];
            $routeConstraintComponents = [];
            $middlewareComponents = [];

            $this->addComponentFromAttribute($routeComponents, $method, Route::class, $controllerComponent);
            $this->addComponentFromAttribute($routeConstraintComponents, $method, RouteConstraint::class, $controllerComponent);

            foreach ([Middleware::class, MiddlewareLibraryMiddleware::class] as $middlewareAttributeClass) {
                $this->addComponentFromAttribute($middlewareComponents, $method, $middlewareAttributeClass, $controllerComponent);
            }

            // Associate all these components as siblings
            $this->addSiblingComponents($routeComponents, $middlewareComponents, $routeConstraintComponents);
            $this->addSiblingComponents($middlewareComponents, $routeComponents, $routeConstraintComponents);
            $this->addSiblingComponents($routeConstraintComponents, $routeComponents, $middlewareComponents);

            $components = [
                ...$components,
                ...$routeComponents,
                ...$middlewareComponents,
                ...$routeConstraintComponents,
            ];
        }

        return $components;
    }

    /**
     * Adds a component from an attribute to the list of components
     *
     * @param list<DiscoveredComponent> $components The list of components to add to
     * @param ReflectionMethod $method The method that the attribute was found on
     * @param string $attributeType The type of attribute to find
     * @param DiscoveredComponent $controllerComponent The controller component that the attribute was found in
     */
    private function addComponentFromAttribute(
        array &$components,
        ReflectionMethod $method,
        string $attributeType,
        DiscoveredComponent $controllerComponent,
    ): void {
        foreach ($method->getAttributes(RouteConstraint::class, ReflectionAttribute::IS_INSTANCEOF) as $routeConstraintAttribute) {
            $components[] = new DiscoveredComponent(
                $this->componentName,
                $controllerComponent->class,
                method: $method,
                attribute: $routeConstraintAttribute,
                parentComponent: $controllerComponent,
            );
        }
    }

    /**
     * Adds sibling components to the list of components
     *
     * @param list<DiscoveredComponent> $components The list of components to add to
     * @param list<DiscoveredComponent> ...$siblingComponents The sibling components to add
     */
    private function addSiblingComponents(array &$components, array ...$siblingComponents): void
    {
        foreach ($components as $component) {
            $component->siblingComponents = \array_merge(...$siblingComponents);
        }
    }
}
