<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Builders;

use Aphiria\Validation\Builders\ObjectConstraintsRegistryBuilder;
use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use PHPUnit\Framework\TestCase;

class ObjectConstraintsRegistryBuilderTest extends TestCase
{
    private ObjectConstraintsRegistryBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ObjectConstraintsRegistryBuilder();
    }

    public function testBuildWithInjectedRegistryRegistersConstraintsToIt(): void
    {
        $injectedObjectConstraints = new ObjectConstraintsRegistry();
        $builder = new ObjectConstraintsRegistryBuilder($injectedObjectConstraints);
        $expectedObjectConstraints = $builder->class(self::class)->build();
        $actualObjectConstraints = $builder->build();
        $this->assertSame($expectedObjectConstraints, $injectedObjectConstraints->getConstraintsForClass(self::class));
        $this->assertSame($expectedObjectConstraints, $actualObjectConstraints->getConstraintsForClass(self::class));
    }

    public function testBuildWithNoSubBuildersCreatesEmptyRegistry(): void
    {
        $expectedObjectConstraints = new ObjectConstraintsRegistry();
        $actualObjectConstraints = $this->builder->build();
        // Purposely checking for loose equality, not reference equality since the two would not reference the same object
        $this->assertEquals($expectedObjectConstraints, $actualObjectConstraints);
    }

    public function testBuildWithMultipleSubBuildersCreatesRegistryWithMultipleObjectConstraints(): void
    {
        $foo = new class() {};
        $bar = new class() {};
        $expectedObjectConstraints = new ObjectConstraintsRegistry();
        $expectedObjectConstraints->registerObjectConstraints(new ObjectConstraints($foo::class));
        $expectedObjectConstraints->registerObjectConstraints(new ObjectConstraints($bar::class));
        $this->builder->class($foo::class);
        $this->builder->class($bar::class);
        $actualObjectConstraints = $this->builder->build();
        // Purposely checking for loose equality, not reference equality since the two would not reference the same object
        $this->assertEquals($expectedObjectConstraints, $actualObjectConstraints);
    }

    public function testBuildWithSingleSubBuildersCreatesRegistryWithSingleObjectConstraints(): void
    {
        $expectedObjectConstraints = new ObjectConstraintsRegistry();
        $expectedObjectConstraints->registerObjectConstraints(new ObjectConstraints(self::class));
        $this->builder->class(self::class);
        $actualObjectConstraints = $this->builder->build();
        // Purposely checking for loose equality, not reference equality since the two would not reference the same object
        $this->assertEquals($expectedObjectConstraints, $actualObjectConstraints);
    }

    public function testClassCreatesBuilderWithCorrectClassName(): void
    {
        $actualConstraintsBuilder = $this->builder->class(self::class);
        $this->assertSame(self::class, $actualConstraintsBuilder->build()->getClassName());
    }
}
