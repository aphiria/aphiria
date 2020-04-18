<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
        $expectedObjectConstraints = $builder->class('foo')->build();
        $actualObjectConstraints = $builder->build();
        $this->assertSame($expectedObjectConstraints, $injectedObjectConstraints->getConstraintsForClass('foo'));
        $this->assertSame($expectedObjectConstraints, $actualObjectConstraints->getConstraintsForClass('foo'));
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
        $expectedObjectConstraints = new ObjectConstraintsRegistry();
        $expectedObjectConstraints->registerObjectConstraints(new ObjectConstraints('foo'));
        $expectedObjectConstraints->registerObjectConstraints(new ObjectConstraints('bar'));
        $this->builder->class('foo');
        $this->builder->class('bar');
        $actualObjectConstraints = $this->builder->build();
        // Purposely checking for loose equality, not reference equality since the two would not reference the same object
        $this->assertEquals($expectedObjectConstraints, $actualObjectConstraints);
    }

    public function testBuildWithSingleSubBuildersCreatesRegistryWithSingleObjectConstraints(): void
    {
        $expectedObjectConstraints = new ObjectConstraintsRegistry();
        $expectedObjectConstraints->registerObjectConstraints(new ObjectConstraints('foo'));
        $this->builder->class('foo');
        $actualObjectConstraints = $this->builder->build();
        // Purposely checking for loose equality, not reference equality since the two would not reference the same object
        $this->assertEquals($expectedObjectConstraints, $actualObjectConstraints);
    }

    public function testClassCreatesBuilderWithCorrectClassName(): void
    {
        $actualConstraintsBuilder = $this->builder->class('foo');
        $this->assertEquals('foo', $actualConstraintsBuilder->build()->getClassName());
    }
}
