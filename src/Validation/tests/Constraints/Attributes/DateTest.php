<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Attributes;

use Aphiria\Validation\Constraints\Attributes\Date;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    public function testCreatingConstraintFromAttributeCreatesCorrectConstraint(): void
    {
        $attribute = new Date(['Ymd']);
        $attribute->createConstraintFromAttribute();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $attribute = new Date(['Ymd']);
        $this->assertNotEmpty($attribute->createConstraintFromAttribute()->errorMessageId);
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $attribute = new Date(['Ymd'], 'foo');
        $this->assertSame('foo', $attribute->createConstraintFromAttribute()->errorMessageId);
    }

    public function testPassingInNoAcceptableFormatsThrowsExceptin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify at least one acceptable date format');
        new Date([]);
    }
}
