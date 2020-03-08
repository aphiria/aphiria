<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Validation\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IDependencyResolver;
use Aphiria\Validation\Builders\ObjectConstraintsBuilderRegistrant;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Closure;
use RuntimeException;

/**
 * Defines the validation component
 */
class ValidationComponent implements IComponent
{
    /** @var IDependencyResolver The dependency resolver */
    private IDependencyResolver $dependencyResolver;
    /** @var bool Whether or not annotations are enabled */
    private bool $annotationsEnabled = false;
    /** @var Closure[] The list of callbacks that can register object constraints */
    private array $callbacks = [];

    /**
     * @param IDependencyResolver $dependencyResolver The dependency resolver
     */
    public function __construct(IDependencyResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
        $objectConstraintsRegistrants = $this->dependencyResolver->resolve(ObjectConstraintsRegistrantCollection::class);

        if ($this->annotationsEnabled) {
            $annotationConstraintsRegistrants = null;

            if (!$this->dependencyResolver->tryResolve(AnnotationObjectConstraintsRegistrant::class, $annotationConstraintsRegistrants)) {
                throw new RuntimeException(AnnotationObjectConstraintsRegistrant::class . ' cannot be null if using annotations');
            }

            $objectConstraintsRegistrants->add($annotationConstraintsRegistrants);
        }

        $objectConstraintsRegistrants->add(new ObjectConstraintsBuilderRegistrant($this->callbacks));
        $objectConstraintsRegistrants->registerConstraints($this->dependencyResolver->resolve(ObjectConstraintsRegistry::class));
    }

    /**
     * Enables support for annotations
     *
     * @return self For chaining
     */
    public function withAnnotations(): self
    {
        $this->annotationsEnabled = true;

        return $this;
    }

    /**
     * Adds an object constraints builder to the collection
     *
     * @param Closure $callback The callback that takes in an instance of ObjectConstraintsRegistryBuilder
     * @return self For chaining
     */
    public function withObjectConstraints(Closure $callback): self
    {
        $this->callbacks[] = $callback;

        return $this;
    }
}
