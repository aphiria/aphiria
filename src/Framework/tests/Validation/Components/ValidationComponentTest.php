<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Validation\Components;

use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Validation\Components\ValidationComponent;
use Aphiria\Validation\Builders\ObjectConstraintsRegistryBuilder;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\Constraints\RequiredConstraint;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ValidationComponentTest extends TestCase
{
    private Container $container;
    private ObjectConstraintsRegistry $objectConstraints;
    private ObjectConstraintsRegistrantCollection $objectConstraintsRegistrants;
    private ValidationComponent $validationComponent;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->bindInstance(ObjectConstraintsRegistry::class, $this->objectConstraints = new ObjectConstraintsRegistry());
        $this->objectConstraintsRegistrants = new class() extends ObjectConstraintsRegistrantCollection {
            public function getAll(): array
            {
                return $this->registrants;
            }
        };
        $this->container->bindInstance(ObjectConstraintsRegistrantCollection::class, $this->objectConstraintsRegistrants);
        $this->validationComponent = new ValidationComponent($this->container);
    }

    public function testBuildRegistersObjectConstraintsRegisteredInCallbacks(): void
    {
        $this->validationComponent->withObjectConstraints(
            fn (ObjectConstraintsRegistryBuilder $objectConstraintsBuilders) => $objectConstraintsBuilders->class('foo')->hasMethodConstraints('bar', new RequiredConstraint())
        );
        $this->validationComponent->build();
        $this->assertInstanceOf(RequiredConstraint::class, $this->objectConstraints->getConstraintsForClass('foo')->getMethodConstraints('bar')[0]);
    }

    public function testBuildWithAnnotationsAddsAnnotationRegistrant(): void
    {
        // We use an empty directory so that we don't actually scan any annotations
        $annotationObjectConstraintsRegistrant = new AnnotationObjectConstraintsRegistrant(__DIR__ . '/files');
        $this->container->bindInstance(AnnotationObjectConstraintsRegistrant::class, $annotationObjectConstraintsRegistrant);
        $this->validationComponent->withAnnotations();
        $this->validationComponent->build();
        // The first should be the annotation registrant, and the second the manually-registered constraint registrant
        $this->assertCount(2, $this->objectConstraintsRegistrants->getAll());
        // Make sure the annotation registrant is first
        $this->assertEquals($annotationObjectConstraintsRegistrant, $this->objectConstraintsRegistrants->getAll()[0]);
    }

    public function testBuildWithAnnotationsWithoutAnnotationRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AnnotationObjectConstraintsRegistrant::class . ' cannot be null if using annotations');
        $this->validationComponent->withAnnotations();
        $this->validationComponent->build();
    }
}
