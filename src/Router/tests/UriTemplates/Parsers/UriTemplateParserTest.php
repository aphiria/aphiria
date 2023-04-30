<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Parsers;

use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\TokenType;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeType;
use Aphiria\Routing\UriTemplates\Parsers\UriTemplateParser;
use PHPUnit\Framework\TestCase;

class UriTemplateParserTest extends TestCase
{
    private UriTemplateParser $parser;

    protected function setUp(): void
    {
        $this->parser = new UriTemplateParser();
    }

    public function testParsingClosingBracketWhenNotParsingOptionalRoutePartTreatsBracketAsText(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Punctuation, ']')
        ]);
        $pathNode = new AstNode(AstNodeType::Path);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, ']'));
        $expectedAst = new AstNode(AstNodeType::Root);
        $expectedAst->addChild($pathNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingInvalidBracketInMiddleOfConstraintThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage("Expected optional path part to start with '/', got " . TokenType::Variable->name);
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Variable, 'foo'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::Punctuation, ']'),
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingNestedOptionalHostPartCreatesNestedOptionalPartNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Text, 'foo'),
            new Token(TokenType::Punctuation, '.'),
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, '.'),
            new Token(TokenType::Punctuation, ']'),
            new Token(TokenType::Punctuation, ']'),
            new Token(TokenType::Text, 'example'),
            new Token(TokenType::Punctuation, '.'),
            new Token(TokenType::Text, 'com'),
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'foo')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $hostNode = new AstNode(AstNodeType::Host, null);
        $expectedAst->addChild($hostNode);
        $innerOptionalRoutePartNode = new AstNode(AstNodeType::OptionalRoutePart, '[');
        $innerOptionalRoutePartNode->addChild(new AstNode(AstNodeType::Text, 'bar'));
        $innerOptionalRoutePartNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '.'));
        $outerOptionalRoutePartNode = new AstNode(AstNodeType::OptionalRoutePart, '[');
        $outerOptionalRoutePartNode->addChild(new AstNode(AstNodeType::Text, 'foo'));
        $outerOptionalRoutePartNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '.'));
        $outerOptionalRoutePartNode->addChild($innerOptionalRoutePartNode);
        $hostNode->addChild($outerOptionalRoutePartNode);
        $hostNode->addChild(new AstNode(AstNodeType::Text, 'example'));
        $hostNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '.'));
        $hostNode->addChild(new AstNode(AstNodeType::Text, 'com'));
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, 'foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingNestedOptionalHostPartThatDoesEndWithPeriodThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage("Expected optional host part to end with '.'");
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Text, 'foo'),
            new Token(TokenType::Punctuation, '.'),
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, ']'),
            new Token(TokenType::Punctuation, ']'),
            new Token(TokenType::Text, 'example'),
            new Token(TokenType::Punctuation, '.'),
            new Token(TokenType::Text, 'com'),
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'foo')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingNestedOptionalPathParts(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'foo'),
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, ']'),
            new Token(TokenType::Punctuation, ']')
        ]);
        $innerOptionalRoutePartNode = new AstNode(AstNodeType::OptionalRoutePart, '[');
        $innerOptionalRoutePartNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $innerOptionalRoutePartNode->addChild(new AstNode(AstNodeType::Text, 'bar'));
        $outerOptionalRoutePartNode = new AstNode(AstNodeType::OptionalRoutePart, '[');
        $outerOptionalRoutePartNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $outerOptionalRoutePartNode->addChild(new AstNode(AstNodeType::Text, 'foo'));
        $outerOptionalRoutePartNode->addChild($innerOptionalRoutePartNode);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild($outerOptionalRoutePartNode);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $expectedAst->addChild($pathNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingNonStandardPunctuationJustGetsTreatedAsText(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Punctuation, '!')
        ]);
        $pathNode = new AstNode(AstNodeType::Path);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, '!'));
        $expectedAst = new AstNode(AstNodeType::Root);
        $expectedAst->addChild($pathNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingNumberOnlyPathCreatesSingleNumber(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Number, 12345)
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, 12345));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingOptionalHostPartThatDoesEndWithPeriodThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage("Expected optional host part to end with '.'");
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Text, 'api'),
            new Token(TokenType::Punctuation, ']'),
            new Token(TokenType::Text, 'example'),
            new Token(TokenType::Punctuation, '.'),
            new Token(TokenType::Text, 'com'),
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'foo')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingOptionalPathPartCreatesCorrectNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'foo'),
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, ']')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, 'foo'));
        $optionalRoutePartNode = new AstNode(AstNodeType::OptionalRoutePart, '[');
        $optionalRoutePartNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $optionalRoutePartNode->addChild(new AstNode(AstNodeType::Text, 'bar'));
        $pathNode->addChild($optionalRoutePartNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingOptionalPathPartThatDoesNotBeginWithSlashThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage("Expected optional path part to start with '/', got " . TokenType::Text->name);
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'foo'),
            new Token(TokenType::Punctuation, '['),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, ']')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingPeriodInPathCreatesTextNode(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Text, 'example'),
            new Token(TokenType::Punctuation, '.'),
            new Token(TokenType::Text, 'com'),
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'foo'),
            new Token(TokenType::Punctuation, '.'),
            new Token(TokenType::Text, 'bar'),
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $hostNode = new AstNode(AstNodeType::Host, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($hostNode);
        $expectedAst->addChild($pathNode);
        $hostNode->addChild(new AstNode(AstNodeType::Text, 'example'));
        $hostNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '.'));
        $hostNode->addChild(new AstNode(AstNodeType::Text, 'com'));
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, 'foo'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, '.'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, 'bar'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingQuotedStringOnlyPathCreatesSingleString(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::QuotedString, '"12345"')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, '"12345"'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingSequentialVariablesThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Cannot have consecutive variables without a delimiter');
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Variable, 'foo'),
            new Token(TokenType::Variable, 'foo')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingTextOnlyPathCreatesSingleTextNode(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Text, 'foo')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Text, 'foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingUnclosedConstraintParenthesisThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Expected closing parenthesis after constraints, got ' . TokenType::Eof->name);
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Variable, 'foo'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::Text, 'bar'),
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingVariableInPathCreatesVariableNameNode(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Variable, 'foo')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild(new AstNode(AstNodeType::Variable, 'foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableInPathWithConstraintButWithNoSlugThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Expected constraint name, got ' . TokenType::Punctuation->name);
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Variable, 'foo'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::Punctuation, ')')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingVariableInPathWithConstraintWithMultipleParametersCreatesCorrectNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Variable, 'foo'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::QuotedString, 'baz'),
            new Token(TokenType::Punctuation, ','),
            new Token(TokenType::Text, 'blah'),
            new Token(TokenType::Punctuation, ')'),
            new Token(TokenType::Punctuation, ')')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $variableConstraintNode = new AstNode(AstNodeType::VariableConstraint, 'bar');
        $variableConstraintNode->addChild(new AstNode(AstNodeType::VariableConstraintParameters, ['baz', 'blah']));
        $variableNode = new AstNode(AstNodeType::Variable, 'foo');
        $variableNode->addChild($variableConstraintNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableInPathWithConstraintWithNoParametersCreatesCorrectNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Variable, 'foo'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, ')')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $variableConstraintNode = new AstNode(AstNodeType::VariableConstraint, 'bar');
        $variableNode = new AstNode(AstNodeType::Variable, 'foo');
        $variableNode->addChild($variableConstraintNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableInPathWithConstraintWithSingleParameterCreatesCorrectNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Variable, 'foo'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::QuotedString, 'baz'),
            new Token(TokenType::Punctuation, ')'),
            new Token(TokenType::Punctuation, ')')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $variableConstraintNode = new AstNode(AstNodeType::VariableConstraint, 'bar');
        $variableConstraintNode->addChild(new AstNode(AstNodeType::VariableConstraintParameters, ['baz']));
        $variableNode = new AstNode(AstNodeType::Variable, 'foo');
        $variableNode->addChild($variableConstraintNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableInPathWithConstraintWithTrailingCommaDoesNotThrowException(): void
    {
        $tokens = new TokenStream([
            new Token(TokenType::Punctuation, '/'),
            new Token(TokenType::Variable, 'foo'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::Text, 'bar'),
            new Token(TokenType::Punctuation, '('),
            new Token(TokenType::QuotedString, 'baz'),
            new Token(TokenType::Punctuation, ','),
            new Token(TokenType::Punctuation, ')'),
            new Token(TokenType::Punctuation, ')')
        ]);
        $expectedAst = new AstNode(AstNodeType::Root, null);
        $pathNode = new AstNode(AstNodeType::Path, null);
        $expectedAst->addChild($pathNode);
        $variableConstraintNode = new AstNode(AstNodeType::VariableConstraint, 'bar');
        $variableConstraintNode->addChild(new AstNode(AstNodeType::VariableConstraintParameters, ['baz']));
        $variableNode = new AstNode(AstNodeType::Variable, 'foo');
        $variableNode->addChild($variableConstraintNode);
        $pathNode->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $pathNode->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }
}
