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

use Aphiria\Validation\Constraints\Annotations\Regex;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RegexTest extends TestCase
{
    public function testCreatingConstraintFromAnnotationCreatesCorrectConstraint(): void
    {
        $annotation = new Regex(['value' => 'regex']);
        $annotation->createConstraintFromAnnotation();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $annotation = new Regex(['value' => 'regex']);
        $this->assertNotEmpty($annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $annotation = new Regex(['value' => 'regex', 'errorMessageId' => 'foo']);
        $this->assertSame('foo', $annotation->createConstraintFromAnnotation()->getErrorMessageId());
    }

    public function testNotSettingValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Regex must be set');
        new Regex([]);
    }

    public function testSettingErrorMessageId(): void
    {
        $annotation = new Regex(['value' => 'regex', 'errorMessageId' => 'foo']);
        $this->assertSame('foo', $annotation->errorMessageId);
    }

    public function testSettingInvalidValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Regex must be set');
        new Regex(['value' => 123]);
    }

    public function testSettingValueSetsRegex(): void
    {
        $annotation = new Regex(['value' => 'foo']);
        $this->assertSame('foo', $annotation->regex);
    }
}
