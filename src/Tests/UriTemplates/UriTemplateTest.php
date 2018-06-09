<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates;

use Opulence\Routing\UriTemplates\Rules\IRule;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Tests the URI template
 */
class UriTemplateTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriTemplate The URI template to use in tests */
    private $uriTemplate;
    /** @var IRule|\PHPUnit_Framework_MockObject_MockObject The rule to use in tests */
    private $rule;

    public function setUp(): void
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

    public function testDefaultValuesAreCorrect(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->uriTemplate->getDefaultRouteVars());
    }

    public function testHttpsOnlyFlagIsCorrect(): void
    {
        $this->assertTrue($this->uriTemplate->isHttpsOnly());
    }

    public function testRegexIsCorrect(): void
    {
        $this->assertEquals('regex', $this->uriTemplate->getRegex());
    }

    public function testRouteVarNamesAreCorrect(): void
    {
        $this->assertEquals(['var'], $this->uriTemplate->getRouteVarNames());
    }

    public function testRuleIsCorrect(): void
    {
        $expectedRules = ['baz' => [$this->rule]];
        $this->assertEquals($expectedRules, $this->uriTemplate->getRouteVarRules());
    }

    public function testSingleRuleIsConvertedToListOfRules(): void
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

    public function testIsRelativeUriReturnsCorrectValue(): void
    {
        $this->assertTrue($this->uriTemplate->isAbsoluteUri());
    }
}
