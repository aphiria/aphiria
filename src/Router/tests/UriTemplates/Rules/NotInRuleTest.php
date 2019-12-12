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

use Aphiria\Routing\UriTemplates\Rules\NotInRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the not-in-array rule
 */
class NotInRuleTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('notIn', NotInRule::getSlug());
    }

    public function testValueInArrayFails(): void
    {
        $rule = new NotInRule(1, 2, 3);
        $this->assertFalse($rule->passes(1));
        $this->assertFalse($rule->passes(2));
        $this->assertFalse($rule->passes(3));
    }

    public function testValueNotInArrayPasses(): void
    {
        $rule = new NotInRule(1, 2, 3);
        $this->assertTrue($rule->passes(4));
        $this->assertTrue($rule->passes(5));
        $this->assertTrue($rule->passes(6));
    }
}
