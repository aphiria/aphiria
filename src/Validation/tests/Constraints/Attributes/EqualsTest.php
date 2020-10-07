<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Attributes;

use Aphiria\Validation\Constraints\Attributes\Equals;
use PHPUnit\Framework\TestCase;

class EqualsTest extends TestCase
{
    public function testCreatingConstraintFromAttributeCreatesCorrectConstraint(): void
    {
        $attribute = new Equals(123);
        $attribute->createConstraintFromAttribute();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $attribute = new Equals(123);
        $this->assertNotEmpty($attribute->createConstraintFromAttribute()->getErrorMessageId());
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $attribute = new Equals(123, 'foo');
        $this->assertSame('foo', $attribute->createConstraintFromAttribute()->getErrorMessageId());
    }
}
