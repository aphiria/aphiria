<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Annotations;

use Aphiria\Validation\Constraints\Annotations\Max;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the max constraint annotation
 */
class MaxTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Max(['value' => 1]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Max(['value' => 1]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testIsInclusiveCanBeSetViaConstructor(): void
    {
        $annotation = new Max(['value' => 2, 'isInclusive' => false]);
        $this->assertFalse($annotation->isInclusive);
    }

    public function testIsInclusiveDefaultsToTrue(): void
    {
        $annotation = new Max(['value' => 2]);
        $this->assertTrue($annotation->isInclusive);
    }

    public function testMaxCanBeSetViaMax(): void
    {
        $annotation = new Max(['max' => 2]);
        $this->assertEquals(2, $annotation->max);
    }

    public function testMaxCanBeSetViaValue(): void
    {
        $annotation = new Max(['value' => 2]);
        $this->assertEquals(2, $annotation->max);
    }

    public function testNotSettingMaxThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max must be set');
        new Max([]);
    }

    public function testNotSettingNumericMaxThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max must be numeric');
        new Max(['value' => 'foo']);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Max(['value' => 1, 'errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }
}
