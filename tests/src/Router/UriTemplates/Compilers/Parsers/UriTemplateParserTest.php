<?php
namespace Opulence\Router\UriTemplates\Compilers\Parsers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\Token;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenTypes;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Router\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;

/**
 * Tests the URI template parser
 */
class UriTemplateParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriTemplateParser The parser to use in tests */
    private $parser = null;

    /**
     * Sets up the tests
     */
    public function setUp()
    {
        $this->parser = new UriTemplateParser();
    }

    /**
     * Test parsing optional route parts creates the correct nodes
     */
    public function testParsingOptionalRoutePartCreatesCorrectNodes() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_TEXT, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, '/bar'),
            new Token(TokenTypes::T_PUNCTUATION, ']')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $expectedAst->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, 'foo'));
        $optionalRoutePartNode = new Node(NodeTypes::OPTIONAL_ROUTE_PART, '[');
        $optionalRoutePartNode->addChild(new Node(NodeTypes::TEXT, '/bar'));
        $expectedAst->getCurrentNode()
            ->addChild($optionalRoutePartNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    /**
     * Tests parsing a text-only route creates a single text node
     */
    public function testParsingTextOnlyRouteCreatesSingleTextNode() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_TEXT, '/foo')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $expectedAst->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    /**
     * Tests parsing a variable name creates a variable name node
     */
    public function testParsingVariableNameCreatesVariableNameNode() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $expectedAst->getCurrentNode()
            ->addChild(new Node(NodeTypes::VARIABLE, 'foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    /**
     * Test parsing invalid bracket in middle of rule throws exception
     */
    public function testParsingInvalidBracketInMiddleOfRuleThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
        ]);
        $this->parser->parse($tokens);
    }

    /**
     * Test parsing unclosed rule parenthesis throws exception
     */
    public function testParsingUnclosedRuleParenthesisThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
        ]);
        $this->parser->parse($tokens);
    }

    /**
     * Tests parsing a variable with a default value and rules creates the correct nodes
     */
    public function testParsingVariableWithDefaultValueAndRulesCreatesCorrectNodes() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '='),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'r1'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'p1'),
            new Token(TokenTypes::T_PUNCTUATION, ')'),
            new Token(TokenTypes::T_PUNCTUATION, ','),
            new Token(TokenTypes::T_TEXT, 'r2'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'p2'),
            new Token(TokenTypes::T_PUNCTUATION, ')'),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $variableRule1Node = new Node(NodeTypes::VARIABLE_RULE, 'r1');
        $variableRule1Node->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, ['p1']));
        $variableRule2Node = new Node(NodeTypes::VARIABLE_RULE, 'r2');
        $variableRule2Node->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, ['p2']));
        $variableNode = new Node(NodeTypes::VARIABLE, 'foo');
        $variableNode->addChild(new Node(NodeTypes::VARIABLE_DEFAULT_VALUE, 'bar'))
            ->addChild($variableRule1Node)
            ->addChild($variableRule2Node);
        $expectedAst->getCurrentNode()
            ->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    /**
     * Tests parsing a variable with an equal sign but no text throws an exception
     */
    public function testParsingVariableWithEqualSignButNoTextThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '=')
        ]);
        $this->parser->parse($tokens);
    }

    /**
     * Tests parsing a variable with a default value creates the correct nodes
     */
    public function testParsingVariableWithDefaultValueCreatesCorrectNodes() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '='),
            new Token(TokenTypes::T_TEXT, 'bar')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $variableNode = new Node(NodeTypes::VARIABLE, 'foo');
        $variableNode->addChild(new Node(NodeTypes::VARIABLE_DEFAULT_VALUE, 'bar'));
        $expectedAst->getCurrentNode()
            ->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    /**
     * Test parsing a variable with a rule with multiple parameters creates the correct nodes
     */
    public function testParsingVariableWithRuleWithMultipleParametersCreatesCorrectNodes() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_QUOTED_STRING, 'baz'),
            new Token(TokenTypes::T_PUNCTUATION, ','),
            new Token(TokenTypes::T_TEXT, 'blah'),
            new Token(TokenTypes::T_PUNCTUATION, ')'),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $variableRuleNode = new Node(NodeTypes::VARIABLE_RULE, 'bar');
        $variableRuleNode->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, ['baz', 'blah']));
        $variableNode = new Node(NodeTypes::VARIABLE, 'foo');
        $variableNode->addChild($variableRuleNode);
        $expectedAst->getCurrentNode()
            ->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    /**
     * Test parsing a variable with a rule with no parameters creates the correct nodes
     */
    public function testParsingVariableWithRuleWithNoParametersCreatesCorrectNodes() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $variableRuleNode = new Node(NodeTypes::VARIABLE_RULE, 'bar');
        $variableNode = new Node(NodeTypes::VARIABLE, 'foo');
        $variableNode->addChild($variableRuleNode);
        $expectedAst->getCurrentNode()
            ->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    /**
     * Test parsing a variable with a rule with a single parameter creates the correct nodes
     */
    public function testParsingVariableWithRuleWithSingleParameterCreatesCorrectNodes() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_QUOTED_STRING, 'baz'),
            new Token(TokenTypes::T_PUNCTUATION, ')'),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $variableRuleNode = new Node(NodeTypes::VARIABLE_RULE, 'bar');
        $variableRuleNode->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, ['baz']));
        $variableNode = new Node(NodeTypes::VARIABLE, 'foo');
        $variableNode->addChild($variableRuleNode);
        $expectedAst->getCurrentNode()
            ->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    /**
     * Test parsing a variable with a rule but with no slug throws an exception
     */
    public function testParsingVariableWithRuleButWithNoSlughrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $this->parser->parse($tokens);
    }

    /**
     * Test parsing a variable with a rule with a trailing comma does not throw an exception
     */
    public function testParsingVariableWithRuleWithTrailingCommaDoesNotThrowException() : void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_QUOTED_STRING, 'baz'),
            new Token(TokenTypes::T_PUNCTUATION, ','),
            new Token(TokenTypes::T_PUNCTUATION, ')'),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $variableRuleNode = new Node(NodeTypes::VARIABLE_RULE, 'bar');
        $variableRuleNode->addChild(new Node(NodeTypes::VARIABLE_RULE_PARAMETERS, ['baz']));
        $variableNode = new Node(NodeTypes::VARIABLE, 'foo');
        $variableNode->addChild($variableRuleNode);
        $expectedAst->getCurrentNode()
            ->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }
}
