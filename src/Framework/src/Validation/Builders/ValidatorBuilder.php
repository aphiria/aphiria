<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Validation\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\Builders\IComponentBuilder;
use Aphiria\Validation\Builders\ObjectConstraintsBuilderRegistrant;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Closure;
use RuntimeException;

/**
 * Defines the validator component builder
 */
class ValidatorBuilder implements IComponentBuilder
{
    /** @var ObjectConstraintsRegistry The object constraints to register to */
    private ObjectConstraintsRegistry $objectConstraints;
    /** @var ObjectConstraintsRegistrantCollection The list of object constraints registrants to add to */
    private ObjectConstraintsRegistrantCollection $objectConstraintsRegistrants;
    /** @var AnnotationObjectConstraintsRegistrant|null The annotation object constraints registrant, if there is one */
    private ?AnnotationObjectConstraintsRegistrant $annotationConstraintsRegistrants;
    /** @var Closure[] The list of callbacks that can register object constraints */
    private array $callbacks = [];

    /**
     * @param ObjectConstraintsRegistry $objectConstraints The object constraints to register to
     * @param ObjectConstraintsRegistrantCollection $constraintsRegistrants The list of object constraints registrants to add to
     * @param AnnotationObjectConstraintsRegistrant|null $annotationConstraintsRegistrants The annotation object constraints registrant, if there is one
     */
    public function __construct(
        ObjectConstraintsRegistry $objectConstraints,
        ObjectConstraintsRegistrantCollection $constraintsRegistrants,
        AnnotationObjectConstraintsRegistrant $annotationConstraintsRegistrants = null
    ) {
        $this->objectConstraints = $objectConstraints;
        $this->objectConstraintsRegistrants = $constraintsRegistrants;
        $this->annotationConstraintsRegistrants = $annotationConstraintsRegistrants;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        $this->objectConstraintsRegistrants->add(new ObjectConstraintsBuilderRegistrant($this->callbacks));
        $this->objectConstraintsRegistrants->registerConstraints($this->objectConstraints);
    }

    /**
     * Enables support for annotations
     *
     * @return ValidatorBuilder For chaining
     * @throws RuntimeException Thrown if there is no annotation registrant
     */
    public function withAnnotations(): self
    {
        if ($this->annotationConstraintsRegistrants === null) {
            throw new RuntimeException(AnnotationObjectConstraintsRegistrant::class . ' cannot be null if using annotations');
        }

        $this->objectConstraintsRegistrants->add($this->annotationConstraintsRegistrants);

        return $this;
    }

    /**
     * Adds an object constraints builder to the collection
     *
     * @param Closure $callback The callback that takes in an instance of ObjectConstraintsRegistryBuilder
     * @return ValidatorBuilder For chaining
     */
    public function withObjectConstraints(Closure $callback): self
    {
        $this->callbacks[] = $callback;

        return $this;
    }
}
