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

use Aphiria\Validation\Constraints\Annotations\Date;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the date constraint annotation
 */
class DateTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Date(['value' => ['Ymd']]);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Date(['value' => ['Ymd']]);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testNotSettingArrayOfAcceptableFormatsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify an acceptable date format');
        new Date([]);
    }

    public function testSettingEmptyArrayOfAcceptableFormatsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify at least one acceptable date format');
        new Date(['value' => []]);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Date(['value' => 'Ymd', 'errorMessageId' => 'foo']);
        $this->assertEquals('foo', $annotation->errorMessageId);
    }

    public function testSettingStringAcceptableFormatIsAccepted(): void
    {
        $annotation = new Date(['value' => 'foo']);
        $this->assertEquals(['foo'], $annotation->acceptableFormats);
    }

    public function testValuesCanBeSetViaValue(): void
    {
        $annotation = new Date(['value' => ['foo']]);
        $this->assertEquals(['foo'], $annotation->acceptableFormats);
    }
}
