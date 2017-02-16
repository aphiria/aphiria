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
    public function testDefaultValueCreatesMatch()
    {
        $template = new RegexUriTemplate('#^/foo/(?P<bar>.+)?$#', ['bar' => 'baz']);
        $routeVars = [];
        $this->assertTrue($template->tryMatch('/foo/', $routeVars));
        $this->assertEquals('baz', $routeVars['bar']);
    }
    
    /**
     * Tests a simple matching URI
     */
    public function testMatchingUri()
    {
        
    }
    
    /**
     * Tests a matching URI but a non-matching rule
     */
    public function testMatchingUriButNonMatchingRule()
    {
        
    }
    
    /**
     * Tests a matching URI with a matching rule
     */
    public function testMatchingUriWithMatchingRule()
    {
        
    }
    
    /**
     * Tests a non-matching URI
     */
    public function testNonMatchingUri()
    {
        
    }
}
