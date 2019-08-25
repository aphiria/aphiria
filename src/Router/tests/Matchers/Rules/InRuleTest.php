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

use Aphiria\Routing\Matchers\Rules\InRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the in-array rule
 */
class InRuleTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('in', InRule::getSlug());
    }

    public function testValueInArrayPasses(): void
    {
        $rule = new InRule(1, 2, 3);
        $this->assertTrue($rule->passes(1));
        $this->assertTrue($rule->passes(2));
        $this->assertTrue($rule->passes(3));
    }

    public function testValueNotInArrayFails(): void
    {
        $rule = new InRule(1, 2, 3);
        $this->assertFalse($rule->passes(4));
        $this->assertFalse($rule->passes(5));
        $this->assertFalse($rule->passes(6));
    }
}
