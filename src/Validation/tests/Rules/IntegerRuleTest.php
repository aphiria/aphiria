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

use Aphiria\Validation\Rules\IntegerRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the integer rule
 */
class IntegerRuleTest extends TestCase
{
    public function testFailingValue(): void
    {
        $rule = new IntegerRule();
        $this->assertFalse($rule->passes(false));
        $this->assertFalse($rule->passes('foo'));
        $this->assertFalse($rule->passes(1.5));
        $this->assertFalse($rule->passes('1.5'));
    }

    public function testGettingSlug(): void
    {
        $rule = new IntegerRule();
        $this->assertEquals('integer', $rule->getSlug());
    }

    public function testPassingValue(): void
    {
        $rule = new IntegerRule();
        $this->assertTrue($rule->passes(0));
        $this->assertTrue($rule->passes(1));
    }
}
