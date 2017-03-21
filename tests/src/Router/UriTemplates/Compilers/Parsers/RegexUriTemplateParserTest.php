<?php
namespace Opulence\Router\UriTemplates\Compilers\Parsers;

use Opulence\Router\UriTemplates\RegexUriTemplate;
use Opulence\Router\UriTemplates\Rules\IRule;
use Opulence\Router\UriTemplates\Rules\IRuleFactory;

/**
 * Tests the regex URI template parser
 */
class RegexUriTemplateParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var RegexUriTemplateParser The parser to use in tests */
    private $parser = null;
    /** @var IRuleFactory|\PHPUnit_Framework_MockObject_MockObject The rule factory to use in the parser */
    private $ruleFactory = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->ruleFactory = $this->createMock(IRuleFactory::class);
        $this->parser = new RegexUriTemplateParser($this->ruleFactory);
    }

    /**
     * Tests that parsing a path with no vars creates the correct regex
     */
    public function testParsingPathWithNoVarsCreatesCorrectRegex() : void
    {
        $expectedUriTemplate = new RegexUriTemplate($this->createUriRegex(null, '/foo/bar/baz'));
        $actualUriTemplate = $this->parser->parse('/foo/bar/baz');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that parsing a path with a single optional var creates the correct regex
     */
    public function testParsingPathWithSingleOptionalWithDefaultValueCreatesCorrectRegex() : void
    {
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo(?:/{$this->createVarRegex('bar', true)})?")
        );
        $actualUriTemplate = $this->parser->parse('/foo[/:bar]');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that parsing a path with a single var creates the correct regex
     */
    public function testParsingPathWithSingleVarCreatesCorrectRegex() : void
    {
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}/baz")
        );
        $actualUriTemplate = $this->parser->parse('/foo/:bar/baz');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that parsing a path with a single var with a default value creates the correct regex
     */
    public function testParsingPathWithSingleVarWithDefaultValueCreatesCorrectRegex() : void
    {
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}/baz"),
            ['bar' => 'blah']
        );
        $actualUriTemplate = $this->parser->parse('/foo/:bar=blah/baz');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that parsing a route var with multiple rules creates the correct template
     */
    public function testParsingRouteVarWithMultipleRulesCreatesCorrectTemplate() : void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule2 = $this->createMock(IRule::class);
        $this->ruleFactory->expects($this->at(0))
            ->method('createRule')
            ->with('dave')
            ->willReturn($rule1);
        $this->ruleFactory->expects($this->at(1))
            ->method('createRule')
            ->with('alex')
            ->willReturn($rule2);
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}"),
            [],
            ['bar' => [$rule1, $rule2]]
        );
        $actualUriTemplate = $this->parser->parse('/foo/:bar(dave,alex)');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that parsing a route var with multiple rules that contain a comma creates the correct template
     */
    public function testParsingRouteVarWithMultipleRulesThatContainCommaCreatesCorrectTemplate() : void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule2 = $this->createMock(IRule::class);
        $this->ruleFactory->expects($this->at(0))
            ->method('createRule')
            ->with('dave', ['1,2'])
            ->willReturn($rule1, ['3', '4']);
        $this->ruleFactory->expects($this->at(1))
            ->method('createRule')
            ->with('alex')
            ->willReturn($rule2);
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}"),
            [],
            ['bar' => [$rule1, $rule2]]
        );
        $actualUriTemplate = $this->parser->parse('/foo/:bar(dave("1,2"),alex(3, 4))');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that parsing a route var with a single rule creates the correct template
     */
    public function testParsingRouteVarWithSingleRuleCreatesCorrectTemplate() : void
    {
        $rule = $this->createMock(IRule::class);
        $this->ruleFactory->expects($this->once())
            ->method('createRule')
            ->with('dave')
            ->willReturn($rule);
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}"),
            [],
            ['bar' => [$rule]]
        );
        $actualUriTemplate = $this->parser->parse('/foo/:bar(dave)');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that parsing a route var with a single rule with parameters creates the correct template
     */
    public function testParsingRouteVarWithSingleWithParamsRuleCreatesCorrectTemplate() : void
    {
        $rule = $this->createMock(IRule::class);
        $this->ruleFactory->expects($this->once())
            ->method('createRule')
            ->with('dave', ['alex', 'lindsey'])
            ->willReturn($rule);
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}"),
            [],
            ['bar' => [$rule]]
        );
        $actualUriTemplate = $this->parser->parse('/foo/:bar(dave(alex,lindsey))');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Creates a regex for a route variable
     *
     * @param string $varName The name of the variable
     * @param bool $isOptional Whether or not the variable is optional
     * @return string The variable regex
     */
    private function createVarRegex(string $varName, bool $isOptional = false) : string
    {
        return "(?P<$varName>[^\/:]+)";
    }

    /**
     * Creates a URI regex
     *
     * @param string $hostRegex The host regex
     * @param string $pathRegex The path regex
     * @param bool $isHttpsOnly Whether or not the URI is HTTPS-only
     * @return string The URI regex
     */
    private function createUriRegex(?string $hostRegex, string $pathRegex, bool $isHttpsOnly = false) : string
    {
        return '#^http' . ($isHttpsOnly ? 's' : '(?:s)?') . '://' . ($hostRegex ?? '[^/]+') . $pathRegex . '$#';
    }
}
