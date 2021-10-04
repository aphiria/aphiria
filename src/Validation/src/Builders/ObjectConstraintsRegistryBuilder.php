<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Builders;

use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;

/**
 * Defines the builder for object constraints registries
 */
final class ObjectConstraintsRegistryBuilder
{
    /** @var list<ObjectConstraintsBuilder> The constraints builders we've created */
    private array $objectConstraintsBuilders = [];

    /**
     * @param ObjectConstraintsRegistry $objectConstraints The constraints to add to, or null if building a new registry
     */
    public function __construct(
        private readonly ObjectConstraintsRegistry $objectConstraints = new ObjectConstraintsRegistry()
    ) {
    }

    /**
     * Builds the object constraints
     *
     * @return ObjectConstraintsRegistry The built object constraints
     */
    public function build(): ObjectConstraintsRegistry
    {
        foreach ($this->objectConstraintsBuilders as $objectConstraintsBuilder) {
            $this->objectConstraints->registerObjectConstraints($objectConstraintsBuilder->build());
        }

        return $this->objectConstraints;
    }

    /**
     * Starts building constraints for a particular class
     *
     * @param class-string $className The name of the class whose constraints we'll build
     * @return ObjectConstraintsBuilder For chaining
     */
    public function class(string $className): ObjectConstraintsBuilder
    {
        $objectConstraintsBuilder = new ObjectConstraintsBuilder($className);
        $this->objectConstraintsBuilders[] = $objectConstraintsBuilder;

        return $objectConstraintsBuilder;
    }
}
