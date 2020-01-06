<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\EmailConstraint;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the email constraint
 */
class EmailConstraintTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $constraint = new EmailConstraint('foo');
        $this->assertEquals('foo', $constraint->getErrorMessageId());
    }

    public function testGettingErrorMessagePlaceholdersIncludesValue(): void
    {
        $this->assertEquals(['value' => 'val'], (new EmailConstraint)->getErrorMessagePlaceholders('val'));
    }

    public function testInvalidEmailFails(): void
    {
        $context = new ValidationContext($this);
        $constraint = new EmailConstraint('foo');
        $this->assertFalse($constraint->passes('foo', $context));
    }

    public function testValidEmailPasses(): void
    {
        $context = new ValidationContext($this);
        $constraint = new EmailConstraint('foo');
        $this->assertTrue($constraint->passes('foo@bar.com', $context));
    }
}
