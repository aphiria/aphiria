<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Rules;

use Aphiria\Validation\Rules\EmailRule;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the email rule
 */
class EmailRuleTest extends TestCase
{
    public function testGettingErrorMessageId(): void
    {
        $rule = new EmailRule('foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testInvalidEmailFails(): void
    {
        $context = new ValidationContext($this);
        $rule = new EmailRule('foo');
        $this->assertFalse($rule->passes('foo', $context));
    }

    public function testValidEmailPasses(): void
    {
        $context = new ValidationContext($this);
        $rule = new EmailRule('foo');
        $this->assertTrue($rule->passes('foo@bar.com', $context));
    }
}
