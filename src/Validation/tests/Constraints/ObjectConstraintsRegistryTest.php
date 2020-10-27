<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use PHPUnit\Framework\TestCase;

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
        $expectedObjectConstraints = new ObjectConstraints(self::class, [], []);
        $registry1->registerObjectConstraints($expectedObjectConstraints);
        $registry2->copy($registry1);
        $this->assertSame($expectedObjectConstraints, $registry2->getConstraintsForClass(self::class));
    }

    public function testGettingConstraintsForClassWithNoneReturnsNull(): void
    {
        $this->assertNull($this->objectConstraints->getConstraintsForClass(self::class));
    }

    public function testGettingConstraintsForClassWithSomeReturnsThem(): void
    {
        $expectedObjectConstraints = new ObjectConstraints(self::class, [], []);
        $this->objectConstraints->registerObjectConstraints($expectedObjectConstraints);
        $this->assertSame($expectedObjectConstraints, $this->objectConstraints->getConstraintsForClass(self::class));
    }
}
