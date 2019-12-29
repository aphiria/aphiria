<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Builders;

use Aphiria\Validation\Builders\ObjectConstraintsBuilderRegistrant;
use Aphiria\Validation\Builders\ObjectConstraintsRegistryBuilder;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the closure constraint registrant
 */
class ObjectConstraintsBuilderRegistrantTest extends TestCase
{
    public function testRegisteringConstraintsRegistersConstraintsFromClosures(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $closures = [function (ObjectConstraintsRegistryBuilder $objectConstraintsRegistryBuilder) use ($expectedConstraint) {
            $objectConstraintsRegistryBuilder->class('foo')
                ->hasPropertyConstraints('prop', $expectedConstraint);
        }];
        $closureConstraintRegistrant = new ObjectConstraintsBuilderRegistrant($closures);
        $objectConstraints = new ObjectConstraintsRegistry();
        $closureConstraintRegistrant->registerConstraints($objectConstraints);
        $propertyConstraints = $objectConstraints->getConstraintsForClass('foo')->getPropertyConstraints('prop');
        $this->assertCount(1, $propertyConstraints);
        $this->assertSame($expectedConstraint, $propertyConstraints[0]);
    }
}
