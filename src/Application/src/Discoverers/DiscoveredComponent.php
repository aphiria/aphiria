<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Discoverers;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Defines a discovered component
 */
class DiscoveredComponent
{
    /**
     * @param ReflectionClass $class The class that was discovered
     * @param ReflectionMethod|null $method The method that was discovered if there was one, otherwise null
     * @param ReflectionProperty|null $property The property that was discovered if there was one, otherwise null
     * @param ReflectionAttribute|null $attribute The attribute that was discovered if there was one, otherwise null
     * @param list<DiscoveredComponent> $siblingComponents The sibling components that were discovered under this component, if any
     * @param list<DiscoveredComponent> $childComponents The child components that were discovered under this component, if any
     */
    public function __construct(
        public protected(set) ReflectionClass $class,
        public protected(set) ?ReflectionMethod $method = null,
        public protected(set) ?ReflectionProperty $property = null,
        public protected(set) ?ReflectionAttribute $attribute = null,
        public protected(set) ?DiscoveredComponent $parentComponent = null,
        public array $siblingComponents = [],
        public array $childComponents = [],
    ) {}

    /**
     * @return array{className: string, methodName: string|null, propertyName: string|null, attributeName: string|null, attributeArgs: array, parentComponent: array|null, siblingComponents: list<array>, childComponents: list<array>}
     */
    public function __serialize(): array
    {
        return [
            'className' => $this->class->name,
            'methodName' => $this->method?->name,
            'propertyName' => $this->property?->name,
            'attributeName' => $this->attribute?->getName(),
            'attributeArgs' => $this->attribute?->getArguments() ?? [],
            'parentComponent' => $this->parentComponent?->__serialize(),
            'siblingComponents' => \array_map(static fn(DiscoveredComponent $component): array => $component->__serialize(), $this->siblingComponents),
            'childComponents' => \array_map(static fn(DiscoveredComponent $component): array => $component->__serialize(), $this->childComponents),
        ];
    }

    /**
     * @param array{className: string, methodName: string|null, propertyName: string|null, attributeName: string|null, attributeArgs: array, parentComponent: array|null, siblingComponents: list<array>, childComponents: list<array>} $data
     * @throws ReflectionException Thrown if the class could not be reflected
     */
    public function __unserialize(array $data): void
    {
        $this->class = new ReflectionClass($data['className']);
        $this->method = $data['methodName'] !== null ? $this->class->getMethod($data['methodName']) : null;
        $this->property = $data['propertyName'] !== null ? $this->class->getProperty($data['propertyName']) : null;
        $this->attribute = $data['attributeName'] !== null && $this->method !== null
            ? ($this->method->getAttributes($data['attributeName'])[0] ?? null)
            : ($data['attributeName'] !== null && $this->property !== null
                ? ($this->property->getAttributes($data['attributeName'])[0] ?? null)
                : ($data['attributeName'] !== null
                    ? ($this->class->getAttributes($data['attributeName'])[0] ?? null)
                    : null));
        $this->parentComponent = $data['parentComponent'] !== null
            ? $this->unserializeComponent($data['parentComponent'])
            : null;
        $this->siblingComponents = \array_map(
            fn(array $componentData): DiscoveredComponent => $this->unserializeComponent($componentData),
            $data['siblingComponents'],
        );
        $this->childComponents = \array_map(
            fn(array $componentData): DiscoveredComponent => $this->unserializeComponent($componentData),
            $data['childComponents'],
        );
    }

    /**
     * Unserializes a single component from array data
     *
     * @param array{className: string, methodName: string|null, propertyName: string|null, attributeName: string|null, attributeArgs: array, parentComponent: array|null, siblingComponents: list<array>, childComponents: list<array>} $data
     * @return self
     * @throws ReflectionException Thrown if the class could not be reflected
     */
    private function unserializeComponent(array $data): self
    {
        $component = new self(new ReflectionClass('stdClass'));
        $component->__unserialize($data);

        return $component;
    }
}
