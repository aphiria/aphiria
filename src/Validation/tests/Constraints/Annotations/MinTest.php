<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Annotations;

use Aphiria\Validation\Constraints\Annotations\Min;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MinTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Min(['value' => 1]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Min(['value' => 1]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $annotation = new Min(['value' => 1, 'errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testIsInclusiveCanBeSetViaConstructor(): void
    {
        $annotation = new Min(['value' => 2, 'isInclusive' => false]);
        $this->assertFalse($annotation->isInclusive);
    }

    public function testIsInclusiveDefaultsToTrue(): void
    {
        $annotation = new Min(['value' => 2]);
        $this->assertTrue($annotation->isInclusive);
    }

    public function testMaxCanBeSetViaMax(): void
    {
        $annotation = new Min(['min' => 2]);
        $this->assertEquals(2, $annotation->min);
    }

    public function testMaxCanBeSetViaValue(): void
    {
        $annotation = new Min(['value' => 2]);
        $this->assertEquals(2, $annotation->min);
    }

    public function testNotSettingMaxThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min must be set');
        new Min([]);
    }

    public function testNotSettingNumericMaxThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min must be numeric');
        new Min(['value' => 'foo']);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Min(['value' => 1, 'errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }
}
