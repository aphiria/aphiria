<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\ClosureObjectConstraintRegistrant;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use Aphiria\Validation\Constraints\ObjectConstraints;
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
            $objectConstraints->registerObjectConstraints(new ObjectConstraints('foo', ['prop' => $expectedConstraint], []));
        }];
        $closureConstraintRegistrant = new ClosureObjectConstraintRegistrant($closures);
        $objectConstraints = new ObjectConstraintRegistry();
        $closureConstraintRegistrant->registerConstraints($objectConstraints);
        $this->assertCount(1, $objectConstraints->getConstraintsForClass('foo')->getPropertyConstraints('prop'));
        $this->assertSame(
            $expectedConstraint,
            $objectConstraints->getConstraintsForClass('foo')->getPropertyConstraints('prop')[0]
        );
    }
}
