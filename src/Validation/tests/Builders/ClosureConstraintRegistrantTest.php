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
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the closure constraint registrant
 */
class ClosureConstraintRegistrantTest extends TestCase
{
    public function testRegisteringConstraintsRegistersConstraintsFromClosures(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $closures = [function (ObjectConstraintRegistry $objectConstraints) use ($expectedConstraint) {
            $objectConstraints->registerObjectConstraints('foo', ['prop' => $expectedConstraint], []);
        }];
        $closureConstraintRegistrant = new ObjectConstraintsBuilderRegistrant($closures);
        $objectConstraints = new ObjectConstraintRegistry();
        $closureConstraintRegistrant->registerConstraints($objectConstraints);
        $this->assertCount(1, $objectConstraints->getPropertyConstraints('foo', 'prop'));
        $this->assertSame(
            $expectedConstraint,
            $objectConstraints->getPropertyConstraints('foo', 'prop')[0]
        );
    }
}
