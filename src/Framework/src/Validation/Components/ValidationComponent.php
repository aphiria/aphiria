<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Validation\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Validation\Builders\ObjectConstraintsBuilderRegistrant;
use Aphiria\Validation\Builders\ObjectConstraintsRegistryBuilder;
use Aphiria\Validation\Constraints\Attributes\AttributeObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Closure;
use RuntimeException;

/**
 * Defines the validation component
 */
class ValidationComponent implements IComponent
{
    /** @var bool Whether or not attributes are enabled */
    private bool $attributesEnabled = false;
    /** @var array<Closure(ObjectConstraintsRegistryBuilder): void> The list of callbacks that can register object constraints */
    private array $callbacks = [];

    /**
     * @param IServiceResolver $serviceResolver The service resolver
     */
    public function __construct(private readonly IServiceResolver $serviceResolver)
    {
    }

    /**
     * @inheritdoc
     * @throws ResolutionException Thrown if any dependencies could not be resolved
     */
    public function build(): void
    {
        $objectConstraintsRegistrants = $this->serviceResolver->resolve(ObjectConstraintsRegistrantCollection::class);

        if ($this->attributesEnabled) {
            $attributeConstraintsRegistrants = null;

            if (!$this->serviceResolver->tryResolve(AttributeObjectConstraintsRegistrant::class, $attributeConstraintsRegistrants)) {
                throw new RuntimeException(AttributeObjectConstraintsRegistrant::class . ' cannot be null if using attributes');
            }

            $objectConstraintsRegistrants->add($attributeConstraintsRegistrants);
        }

        $objectConstraintsRegistrants->add(new ObjectConstraintsBuilderRegistrant($this->callbacks));
        $objectConstraintsRegistrants->registerConstraints($this->serviceResolver->resolve(ObjectConstraintsRegistry::class));
    }

    /**
     * Enables support for attributes
     *
     * @return static For chaining
     */
    public function withAttributes(): static
    {
        $this->attributesEnabled = true;

        return $this;
    }

    /**
     * Adds an object constraints builder to the collection
     *
     * @param Closure(ObjectConstraintsRegistryBuilder): void $callback The callback that takes in an instance of ObjectConstraintsRegistryBuilder
     * @return static For chaining
     */
    public function withObjectConstraints(Closure $callback): static
    {
        $this->callbacks[] = $callback;

        return $this;
    }
}
