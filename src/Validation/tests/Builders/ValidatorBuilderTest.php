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

use Aphiria\Validation\Builders\ValidatorBuilder;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use Aphiria\Validation\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validator builder
 */
class ValidatorBuilderTest extends TestCase
{
    private ObjectConstraintRegistry $objectConstraints;
    private ValidatorBuilder $validatorBuilder;

    protected function setUp(): void
    {
        $this->objectConstraints = new ObjectConstraintRegistry();
        $this->validatorBuilder = new ValidatorBuilder($this->objectConstraints);
    }

    public function testBuildAlsoBuildsObjectConstraintBuilders(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $this->validatorBuilder->class('foo')
            ->hasProperty('prop')
            ->withConstraint($expectedConstraint);
        $this->validatorBuilder->build();
        $this->assertSame(
            [$expectedConstraint],
            $this->objectConstraints->getConstraintsForClass('foo')->getPropertyConstraints('prop')
        );
    }

    public function testBuildCreatesInstanceOfValidator(): void
    {
        $this->assertInstanceOf(Validator::class, $this->validatorBuilder->build());
    }
}
