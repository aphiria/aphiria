<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\ClosureConstraintRegistrant;
use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\Constraints\IValidationConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the closure constraint registrant
 */
class ClosureConstraintRegistrantTest extends TestCase
{
    public function testRegisteringConstraintsRegistersConstraintsFromClosures(): void
    {
        $expectedConstraint = $this->createMock(IValidationConstraint::class);
        $closures = [function (ConstraintRegistry $constraints) use ($expectedConstraint) {
            $constraints->registerPropertyConstraints('foo', 'prop', $expectedConstraint);
        }];
        $closureConstraintRegistrant = new ClosureConstraintRegistrant($closures);
        $constraints = new ConstraintRegistry();
        $closureConstraintRegistrant->registerConstraints($constraints);
        $this->assertCount(1, $constraints->getPropertyConstraints('foo', 'prop'));
        $this->assertSame($expectedConstraint, $constraints->getPropertyConstraints('foo', 'prop')[0]);
    }
}
