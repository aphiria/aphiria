<?php
namespace Opulence\Router\UriTemplates;

use Opulence\Router\UriTemplates\Rules\IRule;

/**
 * Tests the regex URI template
 */
class RegexUriTemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that a default variable value creates a match
     */
    public function testDefaultValueCreatesMatch() : void
    {
        $template = new RegexUriTemplate('#^/foo/(?P<bar>.+)?$#', ['bar' => 'baz']);
        $routeVars = [];
        $this->assertTrue($template->tryMatch('/foo/', $routeVars));
        $this->assertEquals('baz', $routeVars['bar']);
    }

    /**
     * Test that a default variable value does not override a defined value
     */
    public function testDefaultValueDoesNotOverrideDefinedValue() : void
    {
        $template = new RegexUriTemplate('#^/foo/(?P<bar>.+)?$#', ['bar' => 'baz']);
        $routeVars = [];
        $this->assertTrue($template->tryMatch('/foo/blah', $routeVars));
        $this->assertEquals('blah', $routeVars['bar']);
    }

    /**
     * Tests a simple matching URI
     */
    public function testMatchingUri() : void
    {
        $template = new RegexUriTemplate('#^/foo/(?P<bar>.+)?$#');
        $routeVars = [];
        $this->assertTrue($template->tryMatch('/foo/baz', $routeVars));
        $this->assertEquals('baz', $routeVars['bar']);
    }

    /**
     * Tests a matching URI but a non-matching rule
     */
    public function testMatchingUriButNonMatchingRule() : void
    {
        $failingRule = $this->createMock(IRule::class);
        $failingRule->expects($this->once())
            ->method('passes')
            ->with('baz')
            ->willReturn(false);
        $template = new RegexUriTemplate('#^/foo/(?P<bar>.+)?$#', [], ['bar' => $failingRule]);
        $routeVars = [];
        $this->assertFalse($template->tryMatch('/foo/baz', $routeVars));
        $this->assertEquals([], $routeVars);
    }

    /**
     * Tests a matching URI with a matching rule
     */
    public function testMatchingUriWithMatchingRule() : void
    {
        $passingRule = $this->createMock(IRule::class);
        $passingRule->expects($this->once())
            ->method('passes')
            ->with('baz')
            ->willReturn(true);
        $template = new RegexUriTemplate('#^/foo/(?P<bar>.+)?$#', [], ['bar' => $passingRule]);
        $routeVars = [];
        $this->assertTrue($template->tryMatch('/foo/baz', $routeVars));
        $this->assertEquals(['bar' => 'baz'], $routeVars);
    }

    /**
     * Tests a matching URI with one matching rule and one failing rule
     */
    public function testMatchingUriWithOneMatchingRuleAndOneFailingRule() : void
    {
        $passingRule = $this->createMock(IRule::class);
        $passingRule->expects($this->once())
            ->method('passes')
            ->with('baz')
            ->willReturn(true);
        $failingRule = $this->createMock(IRule::class);
        $failingRule->expects($this->once())
            ->method('passes')
            ->with('baz')
            ->willReturn(false);
        $template = new RegexUriTemplate('#^/foo/(?P<bar>.+)?$#', [], ['bar' => [$passingRule, $failingRule]]);
        $routeVars = [];
        $this->assertFalse($template->tryMatch('/foo/baz', $routeVars));
        $this->assertEquals([], $routeVars);
    }

    /**
     * Tests a non-matching URI
     */
    public function testNonMatchingUri() : void
    {
        $template = new RegexUriTemplate('#^/foo/(?P<bar>.+)?$#');
        $routeVars = [];
        $this->assertFalse($template->tryMatch('/bar/baz', $routeVars));
        $this->assertEquals([], $routeVars);
    }
}
