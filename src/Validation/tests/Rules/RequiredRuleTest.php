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

use Countable;
use Aphiria\Validation\Rules\RequiredRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the required rule
 */
class RequiredRuleTest extends TestCase
{
    public function testEmptyArrayFails(): void
    {
        $rule = new RequiredRule();
        $this->assertFalse($rule->passes([]));
        $countable = $this->createMock(Countable::class);
        $countable->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->assertFalse($rule->passes($countable));
    }

    public function testGettingSlug(): void
    {
        $rule = new RequiredRule();
        $this->assertEquals('required', $rule->getSlug());
    }

    public function testSetValuePasses(): void
    {
        $rule = new RequiredRule();
        $this->assertTrue($rule->passes(0));
        $this->assertTrue($rule->passes(true));
        $this->assertTrue($rule->passes(false));
        $this->assertTrue($rule->passes('foo'));
    }

    public function testUnsetValueFails(): void
    {
        $rule = new RequiredRule();
        $this->assertFalse($rule->passes(null));
        $this->assertFalse($rule->passes(''));
    }
}
