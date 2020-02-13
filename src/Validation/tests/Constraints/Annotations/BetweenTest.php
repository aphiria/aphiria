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

use Aphiria\Validation\Constraints\Annotations\Between;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the between constraint annotation
 */
class BetweenTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Between(['min' => 1, 'max' => 2]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Between(['min' => 2, 'max' => 2]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testMaxIsInclusiveCanBeSetViaConstructor(): void
    {
        $annotation = new Between(['min' => 1, 'max' => 2, 'maxIsInclusive' => false]);
        $this->assertFalse($annotation->maxIsInclusive);
    }

    public function testMaxIsInclusiveDefaultsToTrue(): void
    {
        $annotation = new Between(['min' => 1, 'max' => 2]);
        $this->assertTrue($annotation->maxIsInclusive);
    }

    public function testMaxIsSetViaConstructor(): void
    {
        $annotation = new Between(['min' => 1, 'max' => 2]);
        $this->assertEquals(2, $annotation->max);
    }

    public function testMinIsInclusiveCanBeSetViaConstructor(): void
    {
        $annotation = new Between(['min' => 1, 'max' => 2, 'minIsInclusive' => false]);
        $this->assertFalse($annotation->minIsInclusive);
    }

    public function testMinIsInclusiveDefaultsToTrue(): void
    {
        $annotation = new Between(['min' => 1, 'max' => 2]);
        $this->assertTrue($annotation->minIsInclusive);
    }

    public function testMinIsSetViaConstructor(): void
    {
        $annotation = new Between(['min' => 1, 'max' => 2]);
        $this->assertEquals(1, $annotation->min);
    }

    public function testNotSettingMaxThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify a numeric max value');
        new Between(['min' => 1]);
    }

    public function testNotSettingMinThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify a numeric min value');
        new Between(['max' => 1]);
    }

    public function testNotSettingNumericMaxThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify a numeric max value');
        new Between(['min' => 1, 'max' => 'foo']);
    }

    public function testNotSettingNumericMinThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify a numeric min value');
        new Between(['min' => 'foo', 'max' => 1]);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Between(['min' => 1, 'max' => 2, 'errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }
}
