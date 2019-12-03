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
    public function testGettingSlug(): void
    {
        $rule = new EmailRule();
        $this->assertEquals('email', $rule->getSlug());
    }

    public function testInvalidEmailFails(): void
    {
        $context = new ValidationContext($this);
        $rule = new EmailRule();
        $this->assertFalse($rule->passes('foo', $context));
    }

    public function testValidEmailPasses(): void
    {
        $context = new ValidationContext($this);
        $rule = new EmailRule();
        $this->assertTrue($rule->passes('foo@bar.com', $context));
    }
}
