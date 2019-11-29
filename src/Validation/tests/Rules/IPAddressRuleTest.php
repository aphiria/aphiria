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
use PHPUnit\Framework\TestCase;

/**
 * Tests the IP address rule
 */
class IPAddressRuleTest extends TestCase
{
    public function testFailingValue(): void
    {
        $rule = new IPAddressRule();
        $this->assertFalse($rule->passes(''));
        $this->assertFalse($rule->passes('123'));
    }

    public function testGettingSlug(): void
    {
        $rule = new IPAddressRule();
        $this->assertEquals('ipAddress', $rule->getSlug());
    }

    public function testPassingValue(): void
    {
        $rule = new IPAddressRule();
        $this->assertTrue($rule->passes('127.0.0.1'));
    }
}
