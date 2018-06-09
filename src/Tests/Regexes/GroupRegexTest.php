<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Regexes;

use Opulence\Routing\Regexes\GroupRegex;

/**
 * Tests the group regex
 */
class GroupRegexTest extends \PHPUnit\Framework\TestCase
{
    /** @var GroupRegex The group regex to test */
    private $regex;

    public function setUp(): void
    {
        $this->regex = new GroupRegex('foo', ['bar']);
    }

    public function testGettingRegexReturnsCorrectValue(): void
    {
        $this->assertEquals('foo', $this->regex->getGroupRegex());
    }

    public function testGettingRoutesByCapturingGroupOffsetsReturnsCorrectValue(): void
    {
        $this->assertEquals(['bar'], $this->regex->getRoutesByCapturingGroupOffsets());
    }
}
