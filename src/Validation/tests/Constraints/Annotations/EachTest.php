<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Annotations;

use Aphiria\Validation\Constraints\Annotations\Each;
use Aphiria\Validation\Constraints\Annotations\IConstraintAnnotation;
use Aphiria\Validation\Constraints\Annotations\Required;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the each constraint annotation
 */
class EachTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Each(['value' => $this->createMock(IConstraintAnnotation::class)]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Each(['value' => $this->createMock(IConstraintAnnotation::class)]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $annotation = new Each(['value' => $this->createMock(IConstraintAnnotation::class), 'errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testNotSettingArrayOfConstraintsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify a constraint');
        new Each([]);
    }

    public function testSettingEmptyArrayOfConstraintsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify at least one constraint');
        new Each(['value' => []]);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Each([
            'value' => [$this->createMock(IConstraintAnnotation::class)],
            'errorMessageId' => 'foo'
        ]);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }

    public function testSettingSingleConstraintIsAccepted(): void
    {
        $expectedConstraint = new Required([]);
        $annotation = new Each(['value' => $expectedConstraint]);
        $this->assertEquals([$expectedConstraint], $annotation->constraints);
    }

    public function testValuesCanBeSetViaValue(): void
    {
        $expectedConstraint = new Required([]);
        $annotation = new Each(['value' => [$expectedConstraint]]);
        $this->assertEquals([$expectedConstraint], $annotation->constraints);
    }
}
