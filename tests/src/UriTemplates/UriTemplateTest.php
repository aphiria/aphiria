<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates;

use Opulence\Routing\Matchers\UriTemplates\Rules\IRule;

/**
 * Tests the URI template
 */
class UriTemplateTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriTemplate The URI template to use in tests */
    private $uriTemplate = null;
    /** @var IRule|\PHPUnit_Framework_MockObject_MockObject The rule to use in tests */
    private $rule = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->rule = $this->createMock(IRule::class);
        $this->uriTemplate = new UriTemplate(
            'regex',
            true,
            ['var'],
            true,
            ['foo' => 'bar'],
            ['baz' => [$this->rule]]
        );
    }

    /**
     * Tests that the default values are correct
     */
    public function testDefaultValuesAreCorrect() : void
    {
        $this->assertEquals(['foo' => 'bar'], $this->uriTemplate->getDefaultRouteVars());
    }

    /**
     * Tests that the HTTPS-only flag is correct
     */
    public function testHttpsOnlyFlagIsCorrect() : void
    {
        $this->assertTrue($this->uriTemplate->isHttpsOnly());
    }

    /**
     * Tests that the regex is correct
     */
    public function testRegexIsCorrect() : void
    {
        $this->assertEquals('regex', $this->uriTemplate->getRegex());
    }

    /**
     * Tests that the route var names are correct
     */
    public function testRouteVarNamesAreCorrect() : void
    {
        $this->assertEquals(['var'], $this->uriTemplate->getRouteVarNames());
    }

    /**
     * Tests that the rule is correct
     */
    public function testRuleIsCorrect() : void
    {
        $expectedRules = ['baz' => [$this->rule]];
        $this->assertEquals($expectedRules, $this->uriTemplate->getRouteVarRules());
    }

    /**
     * Tests that a single rule is converted to a list of rules
     */
    public function testSingleRuleIsConvertedToListOfRules() : void
    {
        $uriTemplate = new UriTemplate(
            'regex',
            false,
            ['baz'],
            false,
            ['foo' => 'bar'],
            ['baz' => $this->rule]
        );
        $expectedRules = ['baz' => [$this->rule]];
        $this->assertEquals($expectedRules, $uriTemplate->getRouteVarRules());
    }

    /**
     * Tests that the is-relative-URI flag returns correctly
     */
    public function testIsRelataiveUriReturnsCorrectValue() : void
    {
        $this->assertTrue($this->uriTemplate->isAbsoluteUri());
    }
}
