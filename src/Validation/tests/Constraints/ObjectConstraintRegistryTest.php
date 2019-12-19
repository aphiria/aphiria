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

use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use Aphiria\Validation\Constraints\ObjectConstraints;
use PHPUnit\Framework\TestCase;

/**
 * Tests the object constraint registry
 */
class ObjectConstraintRegistryTest extends TestCase
{
    private ObjectConstraintRegistry $objectConstraints;

    protected function setUp(): void
    {
        $this->objectConstraints = new ObjectConstraintRegistry();
    }

    public function testCopyEffectivelyDuplicatesAnotherRegistry(): void
    {
        $registry1 = new ObjectConstraintRegistry();
        $registry2 = new ObjectConstraintRegistry();
        $expectedObjectConstraints = new ObjectConstraints('foo', [], []);
        $registry1->registerObjectConstraints($expectedObjectConstraints);
        $registry2->copy($registry1);
        $this->assertSame($expectedObjectConstraints, $registry2->getConstraintsForClass('foo'));
    }

    public function testGettingConstraintsForClassWithNoneReturnsNull(): void
    {
        $this->assertNull($this->objectConstraints->getConstraintsForClass('foo'));
    }

    public function testGettingConstraintsForClassWithSomeReturnsThem(): void
    {
        $expectedObjectConstraints = new ObjectConstraints('foo', [], []);
        $this->objectConstraints->registerObjectConstraints($expectedObjectConstraints);
        $this->assertSame($expectedObjectConstraints, $this->objectConstraints->getConstraintsForClass('foo'));
    }
}
