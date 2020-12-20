<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Validation\Components;

use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Validation\Components\ValidationComponent;
use Aphiria\Validation\Builders\ObjectConstraintsRegistryBuilder;
use Aphiria\Validation\Constraints\Attributes\AttributeObjectConstraintsRegistrant;
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
        $class = new class() {
        };
        $this->validationComponent->withObjectConstraints(
            fn (ObjectConstraintsRegistryBuilder $objectConstraintsBuilders) => $objectConstraintsBuilders->class($class::class)->hasMethodConstraints('bar', new RequiredConstraint())
        );
        $this->validationComponent->build();
        $classConstraints = $this->objectConstraints->getConstraintsForClass($class::class);
        $this->assertNotNull($classConstraints);
        $this->assertInstanceOf(RequiredConstraint::class, $classConstraints->getMethodConstraints('bar')[0]);
    }

    public function testBuildWithAttributesAddsAttributeRegistrant(): void
    {
        // We use an empty directory so that we don't actually scan any attributes
        $attributeObjectConstraintsRegistrant = new AttributeObjectConstraintsRegistrant(__DIR__ . '/files');
        $this->container->bindInstance(AttributeObjectConstraintsRegistrant::class, $attributeObjectConstraintsRegistrant);
        $this->validationComponent->withAttributes();
        $this->validationComponent->build();
        // The first should be the attribute registrant, and the second the manually-registered constraint registrant
        $this->assertCount(2, $this->objectConstraintsRegistrants->getAll());
        // Make sure the attribute registrant is first
        $this->assertEquals($attributeObjectConstraintsRegistrant, $this->objectConstraintsRegistrants->getAll()[0]);
    }

    public function testBuildWithAttributesWithoutAttributeRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AttributeObjectConstraintsRegistrant::class . ' cannot be null if using attributes');
        $this->validationComponent->withAttributes();
        $this->validationComponent->build();
    }
}
