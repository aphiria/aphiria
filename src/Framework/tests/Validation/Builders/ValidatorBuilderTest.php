<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Validation\Builders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\Framework\Validation\Builders\ValidatorBuilder;
use Aphiria\Validation\Builders\ObjectConstraintsRegistryBuilder;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\Constraints\RequiredConstraint;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the validator builder
 */
class ValidatorBuilderTest extends TestCase
{
    private ObjectConstraintsRegistry $objectConstraints;
    private ObjectConstraintsRegistrantCollection $objectConstraintsRegistrants;

    protected function setUp(): void
    {
        $this->objectConstraints = new ObjectConstraintsRegistry();
        $this->objectConstraintsRegistrants = new class() extends ObjectConstraintsRegistrantCollection
        {
            public function getAll(): array
            {
                return $this->registrants;
            }
        };
    }

    public function testBuildRegistersObjectConstraintsRegisteredInCallbacks(): void
    {
        $validatorBuilder = new ValidatorBuilder($this->objectConstraints, $this->objectConstraintsRegistrants);
        $validatorBuilder->withObjectConstraints(
            fn (ObjectConstraintsRegistryBuilder $objectConstraintsBuilders) => $objectConstraintsBuilders->class('foo')->hasMethodConstraints('bar', new RequiredConstraint())
        );
        $validatorBuilder->build($this->createMock(IApplicationBuilder::class));
        $this->assertInstanceOf(RequiredConstraint::class, $this->objectConstraints->getConstraintsForClass('foo')->getMethodConstraints('bar')[0]);
    }

    public function testWithAnnotationsAddsAnnotationRegistrant(): void
    {
        $annotationObjectConstraintsRegistrant = new AnnotationObjectConstraintsRegistrant(__DIR__);
        $validatorBuilder = new ValidatorBuilder($this->objectConstraints, $this->objectConstraintsRegistrants, $annotationObjectConstraintsRegistrant);
        $validatorBuilder->withAnnotations();
        $this->assertEquals([$annotationObjectConstraintsRegistrant], $this->objectConstraintsRegistrants->getAll());
    }

    public function testWithAnnotationsWithoutAnnotationRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AnnotationObjectConstraintsRegistrant::class . ' cannot be null if using annotations');
        $validatorBuilder = new ValidatorBuilder($this->objectConstraints, $this->objectConstraintsRegistrants);
        $validatorBuilder->withAnnotations();
    }
}
