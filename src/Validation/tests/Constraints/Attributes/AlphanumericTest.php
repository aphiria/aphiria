<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Attributes;

use Aphiria\Validation\Constraints\Attributes\Alphanumeric;
use PHPUnit\Framework\TestCase;

class AlphanumericTest extends TestCase
{
    public function testCreatingConstraintFromAttributeCreatesCorrectConstraint(): void
    {
        $attribute = new Alphanumeric();
        $attribute->createConstraintFromAttribute();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $attribute = new Alphanumeric();
        $this->assertNotEmpty($attribute->createConstraintFromAttribute()->getErrorMessageId());
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $attribute = new Alphanumeric('foo');
        $this->assertSame('foo', $attribute->createConstraintFromAttribute()->getErrorMessageId());
    }
}
