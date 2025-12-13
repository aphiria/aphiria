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
    /**
     * @inheritdoc
     */
    public function discover(ReflectionClass $class): array
    {
        /** @var DiscoveredComponent|null $controllerComponent */
        $controllerComponent = null;

        // Only consider classes that extend Controller or use the #[Controller] attribute
        if (!empty($class->getAttributes(ControllerAttribute::class))) {
            $controllerComponent = new DiscoveredComponent($class, attribute: $class->getAttributes(ControllerAttribute::class)[0]);
        } elseif ($class->isSubclassOf(Controller::class)) {
            $controllerComponent = new DiscoveredComponent($class);
        } else {
            return [];
        }

        $controllerComponents = $this->addComponentsForController($controllerComponent);
        $methodComponents = $this->addComponentsForMethods($controllerComponent);

        // Now that we've discovered all method components, be sure to add them as children of the controller component
        $controllerComponent->childComponents = $methodComponents;

        return [$controllerComponent, ...$controllerComponents, ...$methodComponents];
    }

    /**
     * Adds a component from a class attribute to the list of components
     *
     * @param list<DiscoveredComponent> $components The list of components to add to
     * @param ReflectionClass $class The class that the attribute was found on
     * @param class-string $attributeType The type of attribute to find
     */
    private function addComponentFromClassAttribute(
        array &$components,
        ReflectionClass $class,
        string $attributeType,
    ): void {
        foreach ($class->getAttributes($attributeType, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $components[] = new DiscoveredComponent($class, attribute: $attribute);
        }
    }

    /**
     * Adds a component from a method attribute to the list of components
     *
     * @param list<DiscoveredComponent> $components The list of components to add to
     * @param ReflectionMethod $method The method that the attribute was found on
     * @param class-string $attributeType The type of attribute to find
     * @param DiscoveredComponent $controllerComponent The parent component that the attribute was found in
     */
    private function addComponentFromMethodAttribute(
        array &$components,
        ReflectionMethod $method,
        string $attributeType,
        DiscoveredComponent $controllerComponent,
    ): void {
        foreach ($method->getAttributes($attributeType, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $components[] = new DiscoveredComponent(
                $controllerComponent->class,
                method: $method,
                attribute: $attribute,
                parentComponent: $controllerComponent,
            );
        }
    }

    /**
     * Adds components for the controller
     *
     * @param DiscoveredComponent $controllerComponent The controller component
     * @return list<DiscoveredComponent> The list of components that were added
     */
    private function addComponentsForController(DiscoveredComponent $controllerComponent): array
    {
        $controllerComponents = [];

        foreach ([Middleware::class, MiddlewareLibraryMiddleware::class] as $middlewareAttributeClass) {
            $this->addComponentFromClassAttribute($controllerComponents, $controllerComponent->class, $middlewareAttributeClass);
        }

        $this->addComponentFromClassAttribute($controllerComponents, $controllerComponent->class, RouteConstraint::class);
        $this->addSiblingComponents($controllerComponents, $controllerComponents);
        $this->addSiblingComponents($controllerComponent, $controllerComponents);

        return $controllerComponents;
    }

    /**
     * Adds components for all methods in the controller
     *
     * @param DiscoveredComponent $controllerComponent The controller component
     * @return list<DiscoveredComponent> The list of components that were added
     */
    private function addComponentsForMethods(DiscoveredComponent $controllerComponent): array
    {
        $methodComponents = [];

        foreach ($controllerComponent->class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeComponents = [];
            $routeConstraintComponents = [];
            $middlewareComponents = [];

            $this->addComponentFromMethodAttribute($routeComponents, $method, Route::class, $controllerComponent);
            $this->addComponentFromMethodAttribute($routeConstraintComponents, $method, RouteConstraint::class, $controllerComponent);

            foreach ([Middleware::class, MiddlewareLibraryMiddleware::class] as $middlewareAttributeClass) {
                $this->addComponentFromMethodAttribute($middlewareComponents, $method, $middlewareAttributeClass, $controllerComponent);
            }

            // Associate all these components as siblings
            $this->addSiblingComponents($routeComponents, $middlewareComponents, $routeConstraintComponents);
            $this->addSiblingComponents($middlewareComponents, $routeComponents, $routeConstraintComponents);
            $this->addSiblingComponents($routeConstraintComponents, $routeComponents, $middlewareComponents);

            $methodComponents = [
                ...$methodComponents,
                ...$routeComponents,
                ...$middlewareComponents,
                ...$routeConstraintComponents,
            ];
        }

        return $methodComponents;
    }

    /**
     * Adds sibling components to the list of components
     *
     * @param list<DiscoveredComponent>|DiscoveredComponent $components The component or list of components to add to
     * @param list<DiscoveredComponent> ...$siblingComponents The sibling components to add
     */
    private function addSiblingComponents(array|DiscoveredComponent &$components, array ...$siblingComponents): void
    {
        foreach (\is_array($components) ? $components : [$components] as $component) {
            $component->siblingComponents = \array_merge(...$siblingComponents);
        }
    }
}
