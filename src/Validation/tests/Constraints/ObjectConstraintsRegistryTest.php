<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the object constraint registry
 */
class ObjectConstraintsRegistryTest extends TestCase
{
    private ObjectConstraintsRegistry $objectConstraints;

    protected function setUp(): void
    {
        $this->objectConstraints = new ObjectConstraintsRegistry();
    }

    public function testCopyEffectivelyDuplicatesAnotherRegistry(): void
    {
        $registry1 = new ObjectConstraintsRegistry();
        $registry2 = new ObjectConstraintsRegistry();
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
