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

use Aphiria\Validation\Constraints\Annotations\Equals;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EqualsTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Equals(['value' => 'val']);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Equals(['value' => 'val']);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $annotation = new Equals(['value' => 'val', 'errorMessageId' => 'foo']);
        $this->assertSame('foo', $annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testNotSettingValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify a value to compare against');
        new Equals([]);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Equals(['value' => 'val', 'errorMessageId' => 'foo']);
        $this->assertSame('foo', $annotation->errorMessageId);
    }

    public function testSettingNullValueIsAccepted(): void
    {
        $annotation = new Equals(['value' => null]);
        $this->assertNull($annotation->value);
    }

    public function testSettingStringValueIsAccepted(): void
    {
        $annotation = new Equals(['value' => 'foo']);
        $this->assertSame('foo', $annotation->value);
    }
}
