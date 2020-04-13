<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\EmailConstraint;
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
        $this->assertEquals(['value' => 'val'], (new EmailConstraint())->getErrorMessagePlaceholders('val'));
    }

    public function testInvalidEmailFails(): void
    {
        $constraint = new EmailConstraint('foo');
        $this->assertFalse($constraint->passes('foo'));
    }

    public function testValidEmailPasses(): void
    {
        $constraint = new EmailConstraint('foo');
        $this->assertTrue($constraint->passes('foo@bar.com'));
    }
}
