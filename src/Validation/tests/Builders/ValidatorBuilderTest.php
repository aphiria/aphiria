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
use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\Constraints\IValidationConstraint;
use Aphiria\Validation\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validator builder
 */
class ValidatorBuilderTest extends TestCase
{
    private ConstraintRegistry $constraints;
    private ValidatorBuilder $validatorBuilder;

    protected function setUp(): void
    {
        $this->constraints = new ConstraintRegistry();
        $this->validatorBuilder = new ValidatorBuilder($this->constraints);
    }

    public function testAddingConstraintsToClassAddsThemToConstraintRegistry(): void
    {
        $expectedConstraint = $this->createMock(IValidationConstraint::class);
        $this->validatorBuilder->class('foo')
            ->hasProperty('prop')
            ->withConstraint($expectedConstraint);
        $this->assertSame([$expectedConstraint], $this->constraints->getPropertyConstraints('foo', 'prop'));
    }

    public function testBuildCreatesInstanceOfValidator(): void
    {
        $this->assertInstanceOf(Validator::class, $this->validatorBuilder->build());
    }
}
