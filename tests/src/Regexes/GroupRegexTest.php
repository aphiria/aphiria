<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Regexes;

/**
 * Tests the group regex
 */
class GroupRegexTest extends \PHPUnit\Framework\TestCase
{
    /** @var GroupRegex The group regex to test */
    private $regex = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->regex = new GroupRegex('foo', ['bar']);
    }

    /**
     * Tests getting the regex returns the correct value
     */
    public function testGettingRegexReturnsCorrectValue() : void
    {
        $this->assertEquals('foo', $this->regex->getGroupRegex());
    }

    /**
     * Tests getting the routes by capturing group offsets returns the correct value
     */
    public function testGettingRoutesByCapturingGroupOffsetsReturnsCorrectValue() : void
    {
        $this->assertEquals(['bar'], $this->regex->getRoutesByCapturingGroupOffsets());
    }
}
