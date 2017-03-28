<?php
namespace Opulence\Router\UriTemplates\Compilers;

use Opulence\Router\UriTemplates\Compilers\Parsers\AbstractSyntaxTree;
use Opulence\Router\UriTemplates\Compilers\Parsers\IUriTemplateParser;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\IUriTemplateLexer;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;
use Opulence\Router\UriTemplates\RegexUriTemplate;
use Opulence\Router\UriTemplates\Rules\IRule;
use Opulence\Router\UriTemplates\Rules\IRuleFactory;

/**
 * Tests the regex URI template compiler
 */
class RegexUriTemplateCompilerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RegexUriTemplateCompiler The compiler to use in tests */
    private $compiler = null;
    /** @var IUriTemplateParser|\PHPUnit_Framework_MockObject_MockObject The parser to use in tests */
    private $parser = null;
    /** @var IUriTemplateLexer|\PHPUnit_Framework_MockObject_MockObject The lexer to use in tests */
    private $lexer = null;
    /** @var AbstractSyntaxTree The abstract syntax tree to use in tests */
    private $ast = null;
    /** @var IRuleFactory|\PHPUnit_Framework_MockObject_MockObject The rule factory to use in the compiler */
    private $ruleFactory = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->ruleFactory = $this->createMock(IRuleFactory::class);
        $this->parser = $this->createMock(IUriTemplateParser::class);
        $this->lexer = $this->createMock(IUriTemplateLexer::class);
        $this->ast = new AbstractSyntaxTree();
        // We don't really care about mocking the output of the lexer
        $this->lexer->expects($this->any())->method('lex')->willReturn(new TokenStream([]));
        $this->parser->expects($this->any())->method('parse')->willReturn($this->ast);
        $this->compiler = new RegexUriTemplateCompiler($this->ruleFactory, $this->parser, $this->lexer);
    }

    /**
     * Tests compiling a host and path with slashes around them trims the slash between them
     */
    public function testCompilingHostAndPathWithSlashesTrimsSlashBetweenThem() : void
    {
        $expectedUriTemplate = new RegexUriTemplate($this->createUriRegex('foo\.com', '/bar'));
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, 'foo.com/bar'));
        $actualUriTemplate = $this->compiler->compile('/bar', 'foo.com/');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling an HTTPS-only route forces HTTPS to be set in the regex
     */
    public function testCompilingHttpsOnlyRouteForcesHttpsToBeSet() : void
    {
        $expectedUriTemplate = new RegexUriTemplate($this->createUriRegex(null, 'foo', true));
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, 'foo'));
        $actualUriTemplate = $this->compiler->compile('foo', null, true);
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling a path with no vars creates the correct regex
     */
    public function testCompilingPathWithNoVarsCreatesCorrectRegex() : void
    {
        $expectedUriTemplate = new RegexUriTemplate($this->createUriRegex(null, '/foo/bar/baz'));
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo/bar/baz'));
        $actualUriTemplate = $this->compiler->compile('/foo/bar/baz');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling a path with a single optional var creates the correct regex
     */
    public function testCompilingPathWithSingleOptionalVarCreatesCorrectRegex() : void
    {
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo(?:/{$this->createVarRegex('bar', true)})?")
        );
        $optionalRoutePartNode = new Node(NodeTypes::OPTIONAL_ROUTE_PART, '[');
        $optionalRoutePartNode->addChild(new Node(NodeTypes::TEXT, '/'));
        $optionalRoutePartNode->addChild(new Node(NodeTypes::VARIABLE, 'bar'));
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo'));
        $this->ast->getCurrentNode()
            ->addChild($optionalRoutePartNode);
        $actualUriTemplate = $this->compiler->compile('/foo[/:bar]');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling a path with a single var creates the correct regex
     */
    public function testCompilingPathWithSingleVarCreatesCorrectRegex() : void
    {
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}/baz")
        );
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo/'));
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::VARIABLE, 'bar'));
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/baz'));
        $actualUriTemplate = $this->compiler->compile('/foo/:bar/baz');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling a path with a single var with a default value creates the correct regex
     */
    public function testCompilingPathWithSingleVarWithDefaultValueCreatesCorrectRegex() : void
    {
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}/baz"),
            ['bar' => 'blah']
        );
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo/'));
        $variableNode = new Node(NodeTypes::VARIABLE, 'bar');
        $variableNode->addChild(new Node(NodeTypes::VARIABLE_DEFAULT_VALUE, 'blah'));
        $this->ast->getCurrentNode()
            ->addChild($variableNode);
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/baz'));
        $actualUriTemplate = $this->compiler->compile('/foo/:bar=blah/baz');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling a route var with multiple rules creates the correct template
     */
    public function testCompilingRouteVarWithMultipleRulesCreatesCorrectTemplate() : void
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
        $variableNode = new Node(NodeTypes::VARIABLE, 'bar');
        $variableNode->addChild(new Node(NodeTypes::VARIABLE_RULE, 'dave'))
            ->addChild(new Node(NodeTypes::VARIABLE_RULE, 'alex'));
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo/'));
        $this->ast->getCurrentNode()
            ->addChild($variableNode);
        $actualUriTemplate = $this->compiler->compile('/foo/:bar(dave,alex)');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling a route var with multiple rules that contain a comma creates the correct template
     */
    public function testCompilingRouteVarWithMultipleRulesThatContainCommaCreatesCorrectTemplate() : void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule2 = $this->createMock(IRule::class);
        $this->ruleFactory->expects($this->at(0))
            ->method('createRule')
            ->with('dave', ['1,2'])
            ->willReturn($rule1);
        $this->ruleFactory->expects($this->at(1))
            ->method('createRule')
            ->with('alex', [3, 4])
            ->willReturn($rule2);
        $expectedUriTemplate = new RegexUriTemplate(
            $this->createUriRegex(null, "/foo/{$this->createVarRegex('bar')}"),
            [],
            ['bar' => [$rule1, $rule2]]
        );
        $daveRuleNode = new Node(NodeTypes::VARIABLE_RULE, 'dave');
        $daveRuleNode->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, ['1,2']));
        $alexRuleNode = new Node(NodeTypes::VARIABLE_RULE, 'alex');
        $alexRuleNode->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, [3, 4]));
        $variableNode = new Node(NodeTypes::VARIABLE, 'bar');
        $variableNode->addChild($daveRuleNode)
            ->addChild($alexRuleNode);
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo/'));
        $this->ast->getCurrentNode()
            ->addChild($variableNode);
        $actualUriTemplate = $this->compiler->compile('/foo/:bar(dave("1,2"),alex(3, 4))');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling a route var with a single rule creates the correct template
     */
    public function testCompilingRouteVarWithSingleRuleCreatesCorrectTemplate() : void
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
        $variableNode = new Node(NodeTypes::VARIABLE, 'bar');
        $variableNode->addChild(new Node(NodeTypes::VARIABLE_RULE, 'dave'));
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo/'));
        $this->ast->getCurrentNode()
            ->addChild($variableNode);
        $actualUriTemplate = $this->compiler->compile('/foo/:bar(dave)');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
    }

    /**
     * Tests that compiling a route var with a single rule with parameters creates the correct template
     */
    public function testCompilingRouteVarWithSingleWithParamsRuleCreatesCorrectTemplate() : void
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
        $daveRuleNode = new Node(NodeTypes::VARIABLE_RULE, 'dave');
        $daveRuleNode->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, ['alex', 'lindsey']));
        $variableNode = new Node(NodeTypes::VARIABLE, 'bar');
        $variableNode->addChild($daveRuleNode);
        $this->ast->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo/'));
        $this->ast->getCurrentNode()
            ->addChild($variableNode);
        $actualUriTemplate = $this->compiler->compile('/foo/:bar(dave("alex","lindsey"))');
        $this->assertEquals($expectedUriTemplate, $actualUriTemplate);
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
}
