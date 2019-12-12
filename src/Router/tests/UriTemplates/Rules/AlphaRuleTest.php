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

use Aphiria\Routing\UriTemplates\Rules\AlphaRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the alpha rule
 */
class AlphaRuleTest extends TestCase
{
    public function testAlphaCharsPass(): void
    {
        $rule = new AlphaRule();
        $this->assertTrue($rule->passes('a'));
        $this->assertTrue($rule->passes('ab'));
    }

    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('alpha', AlphaRule::getSlug());
    }

    public function testNonAlphaCharsFail(): void
    {
        $rule = new AlphaRule();
        $this->assertFalse($rule->passes(''));
        $this->assertFalse($rule->passes('1'));
        $this->assertFalse($rule->passes('a b'));
    }
}
