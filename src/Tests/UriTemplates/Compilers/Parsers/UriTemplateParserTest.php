<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Compilers\Parsers;

use InvalidArgumentException;
use Opulence\Routing\UriTemplates\Compilers\Parsers\AbstractSyntaxTree;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\Token;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenTypes;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;
use Opulence\Routing\UriTemplates\Compilers\Parsers\UriTemplateParser;

/**
 * Tests the URI template parser
 */
class UriTemplateParserTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriTemplateParser The parser to use in tests */
    private $parser;

    public function setUp()
    {
        $this->parser = new UriTemplateParser();
    }

    public function testParsingInvalidBracketInMiddleOfRuleThrowsException(): void
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

    public function testParsingNestedOptionalRouteParts(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
            new Token(TokenTypes::T_PUNCTUATION, ']')
        ]);
        $innerOptionalRoutePartNode = new Node(NodeTypes::OPTIONAL_ROUTE_PART, '[');
        $innerOptionalRoutePartNode->addChild(new Node(NodeTypes::TEXT, 'bar'));
        $outerOptionalRoutePartNode = new Node(NodeTypes::OPTIONAL_ROUTE_PART, '[');
        $outerOptionalRoutePartNode->addChild(new Node(NodeTypes::TEXT, 'foo'));
        $outerOptionalRoutePartNode->addChild($innerOptionalRoutePartNode);
        $expectedAst = new AbstractSyntaxTree();
        $expectedAst->getCurrentNode()
            ->addChild($outerOptionalRoutePartNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingOptionalRoutePartCreatesCorrectNodes(): void
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

    public function testParsingTextOnlyRouteCreatesSingleTextNode(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_TEXT, '/foo')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $expectedAst->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '/foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingNumberOnlyRouteCreatesSingleNumber(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_NUMBER, 12345)
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $expectedAst->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, 12345));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingQuotedStringOnlyRouteCreatesSingleString(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_QUOTED_STRING, '"12345"')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $expectedAst->getCurrentNode()
            ->addChild(new Node(NodeTypes::TEXT, '"12345"'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingUnclosedRuleParenthesisThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingVariableNameCreatesVariableNameNode(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo')
        ]);
        $expectedAst = new AbstractSyntaxTree();
        $expectedAst->getCurrentNode()
            ->addChild(new Node(NodeTypes::VARIABLE, 'foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableWithDefaultValueAndRulesCreatesCorrectNodes(): void
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

    public function testParsingVariableWithEqualSignButNoTextThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '=')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingVariableWithDefaultValueCreatesCorrectNodes(): void
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

    public function testParsingVariableWithRuleWithMultipleParametersCreatesCorrectNodes(): void
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

    public function testParsingVariableWithRuleWithNoParametersCreatesCorrectNodes(): void
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

    public function testParsingVariableWithRuleWithSingleParameterCreatesCorrectNodes(): void
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

    public function testParsingVariableWithRuleButWithNoSlughrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $tokens = new TokenStream([
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingVariableWithRuleWithTrailingCommaDoesNotThrowException(): void
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
