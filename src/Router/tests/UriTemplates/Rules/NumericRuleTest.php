<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Rules;

use Aphiria\Routing\UriTemplates\Rules\NumericRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the numeric rule
 */
class NumericRuleTest extends TestCase
{
    public function testAlphaCharsPass(): void
    {
        $rule = new NumericRule();
        $this->assertTrue($rule->passes(0));
        $this->assertTrue($rule->passes(1));
        $this->assertTrue($rule->passes(1.0));
        $this->assertTrue($rule->passes('1.0'));
    }

    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('numeric', NumericRule::getSlug());
    }

    public function testNonAlphaCharsFail(): void
    {
        $rule = new NumericRule();
        $this->assertFalse($rule->passes(false));
        $this->assertFalse($rule->passes('foo'));
    }
}
