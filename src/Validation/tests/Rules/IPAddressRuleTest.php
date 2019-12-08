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

use Aphiria\Validation\Rules\IPAddressRule;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the IP address rule
 */
class IPAddressRuleTest extends TestCase
{
    public function testFailingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new IPAddressRule('foo');
        $this->assertFalse($rule->passes('', $context));
        $this->assertFalse($rule->passes('123', $context));
    }

    public function testGettingErrorMessageId(): void
    {
        $rule = new IPAddressRule('foo');
        $this->assertEquals('foo', $rule->getErrorMessageId());
    }

    public function testPassingValue(): void
    {
        $context = new ValidationContext($this);
        $rule = new IPAddressRule('foo');
        $this->assertTrue($rule->passes('127.0.0.1', $context));
    }
}
