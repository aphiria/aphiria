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

use Aphiria\Validation\Constraints\Attributes\Integer;
use PHPUnit\Framework\TestCase;

class IntegerTest extends TestCase
{
    public function testCreatingConstraintFromAttributeCreatesCorrectConstraint(): void
    {
        $attribute = new Integer();
        $attribute->createConstraintFromAttribute();
        // Dummy assertion to just make sure we can actually create the constraint
        $this->assertTrue(true);
    }

    public function testCreatingConstraintHasDefaultErrorMessageId(): void
    {
        $attribute = new Integer();
        $this->assertNotEmpty($attribute->createConstraintFromAttribute()->errorMessageId);
    }

    public function testCreatingConstraintUsesErrorMessageIdIfSpecified(): void
    {
        $attribute = new Integer('foo');
        $this->assertSame('foo', $attribute->createConstraintFromAttribute()->errorMessageId);
    }
}
