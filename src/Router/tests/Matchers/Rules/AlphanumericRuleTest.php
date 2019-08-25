<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers\Rules;

use Aphiria\Routing\Matchers\Rules\AlphanumericRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alphanumeric rule
 */
class AlphanumericRuleTest extends TestCase
{
    public function testAlphanumericCharsPass(): void
    {
        $rule = new AlphanumericRule();
        $this->assertTrue($rule->passes('1'));
        $this->assertTrue($rule->passes('a'));
        $this->assertTrue($rule->passes('a1'));
        $this->assertTrue($rule->passes('1abc'));
    }

    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('alphanumeric', AlphanumericRule::getSlug());
    }

    public function testNonAlphanumericCharsFail(): void
    {
        $rule = new AlphanumericRule();
        $this->assertFalse($rule->passes(''));
        $this->assertFalse($rule->passes('.'));
        $this->assertFalse($rule->passes('a1 b'));
    }
}
