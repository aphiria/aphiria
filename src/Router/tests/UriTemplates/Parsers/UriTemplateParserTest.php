<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Parsers;

use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\TokenTypes;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeTypes;
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
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_PUNCTUATION, ']')
        ]);
        $pathNode = new AstNode(AstNodeTypes::PATH);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, ']'));
        $expectedAst = new AstNode(AstNodeTypes::ROOT);
        $expectedAst->addChild($pathNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingInvalidBracketInMiddleOfConstraintThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage("Expected optional path part to start with '/', got T_VARIABLE");
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingNestedOptionalPathParts(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
            new Token(TokenTypes::T_PUNCTUATION, ']')
        ]);
        $innerOptionalRoutePartNode = new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART, '[');
        $innerOptionalRoutePartNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $innerOptionalRoutePartNode->addChild(new AstNode(AstNodeTypes::TEXT, 'bar'));
        $outerOptionalRoutePartNode = new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART, '[');
        $outerOptionalRoutePartNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $outerOptionalRoutePartNode->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'));
        $outerOptionalRoutePartNode->addChild($innerOptionalRoutePartNode);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild($outerOptionalRoutePartNode);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $expectedAst->addChild($pathNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingNestedOptionalHostPartCreatesNestedOptionalPartNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '.'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, '.'),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
            new Token(TokenTypes::T_TEXT, 'example'),
            new Token(TokenTypes::T_PUNCTUATION, '.'),
            new Token(TokenTypes::T_TEXT, 'com'),
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'foo')
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $hostNode = new AstNode(AstNodeTypes::HOST, null);
        $expectedAst->addChild($hostNode);
        $innerOptionalRoutePartNode = new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART, '[');
        $innerOptionalRoutePartNode->addChild(new AstNode(AstNodeTypes::TEXT, 'bar'));
        $innerOptionalRoutePartNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '.'));
        $outerOptionalRoutePartNode = new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART, '[');
        $outerOptionalRoutePartNode->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'));
        $outerOptionalRoutePartNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '.'));
        $outerOptionalRoutePartNode->addChild($innerOptionalRoutePartNode);
        $hostNode->addChild($outerOptionalRoutePartNode);
        $hostNode->addChild(new AstNode(AstNodeTypes::TEXT, 'example'));
        $hostNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '.'));
        $hostNode->addChild(new AstNode(AstNodeTypes::TEXT, 'com'));
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingNestedOptionalHostPartThatDoesEndWithPeriodThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage("Expected optional host part to end with '.'");
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '.'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
            new Token(TokenTypes::T_TEXT, 'example'),
            new Token(TokenTypes::T_PUNCTUATION, '.'),
            new Token(TokenTypes::T_TEXT, 'com'),
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'foo')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingNonStandardPunctuationJustGetsTreatedAsText(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_PUNCTUATION, '!')
        ]);
        $pathNode = new AstNode(AstNodeTypes::PATH);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, '!'));
        $expectedAst = new AstNode(AstNodeTypes::ROOT);
        $expectedAst->addChild($pathNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingOptionalHostPartThatDoesEndWithPeriodThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage("Expected optional host part to end with '.'");
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, 'api'),
            new Token(TokenTypes::T_PUNCTUATION, ']'),
            new Token(TokenTypes::T_TEXT, 'example'),
            new Token(TokenTypes::T_PUNCTUATION, '.'),
            new Token(TokenTypes::T_TEXT, 'com'),
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'foo')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingOptionalPathPartCreatesCorrectNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, ']')
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'));
        $optionalRoutePartNode = new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART, '[');
        $optionalRoutePartNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $optionalRoutePartNode->addChild(new AstNode(AstNodeTypes::TEXT, 'bar'));
        $pathNode->addChild($optionalRoutePartNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingOptionalPathPartThatDoesNotBeginWithSlashThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage("Expected optional path part to start with '/', got T_TEXT");
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '['),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, ']')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingTextOnlyPathCreatesSingleTextNode(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'foo')
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingNumberOnlyPathCreatesSingleNumber(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_NUMBER, 12345)
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, 12345));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingPeriodInPathCreatesTextNode(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_TEXT, 'example'),
            new Token(TokenTypes::T_PUNCTUATION, '.'),
            new Token(TokenTypes::T_TEXT, 'com'),
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_TEXT, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '.'),
            new Token(TokenTypes::T_TEXT, 'bar'),
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $hostNode = new AstNode(AstNodeTypes::HOST, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($hostNode);
        $expectedAst->addChild($pathNode);
        $hostNode->addChild(new AstNode(AstNodeTypes::TEXT, 'example'));
        $hostNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '.'));
        $hostNode->addChild(new AstNode(AstNodeTypes::TEXT, 'com'));
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, '.'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, 'bar'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingQuotedStringOnlyPathCreatesSingleString(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_QUOTED_STRING, '"12345"')
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::TEXT, '"12345"'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingSequentialVariablesThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Cannot have consecutive variables without a delimiter');
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_VARIABLE, 'foo')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingUnclosedConstraintParenthesisThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Expected closing parenthesis after constraints, got T_EOF');
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingVariableInPathCreatesVariableNameNode(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_VARIABLE, 'foo')
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild(new AstNode(AstNodeTypes::VARIABLE, 'foo'));
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableInPathWithConstraintWithMultipleParametersCreatesCorrectNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
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
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $variableConstraintNode = new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT, 'bar');
        $variableConstraintNode->addChild(new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT_PARAMETERS, ['baz', 'blah']));
        $variableNode = new AstNode(AstNodeTypes::VARIABLE, 'foo');
        $variableNode->addChild($variableConstraintNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableInPathWithConstraintWithNoParametersCreatesCorrectNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $variableConstraintNode = new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT, 'bar');
        $variableNode = new AstNode(AstNodeTypes::VARIABLE, 'foo');
        $variableNode->addChild($variableConstraintNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableInPathWithConstraintWithSingleParameterCreatesCorrectNodes(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_QUOTED_STRING, 'baz'),
            new Token(TokenTypes::T_PUNCTUATION, ')'),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $variableConstraintNode = new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT, 'bar');
        $variableConstraintNode->addChild(new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT_PARAMETERS, ['baz']));
        $variableNode = new AstNode(AstNodeTypes::VARIABLE, 'foo');
        $variableNode->addChild($variableConstraintNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }

    public function testParsingVariableInPathWithConstraintButWithNoSlugThrowsException(): void
    {
        $this->expectException(UnexpectedTokenException::class);
        $this->expectExceptionMessage('Expected constraint name, got T_PUNCTUATION');
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $this->parser->parse($tokens);
    }

    public function testParsingVariableInPathWithConstraintWithTrailingCommaDoesNotThrowException(): void
    {
        $tokens = new TokenStream([
            new Token(TokenTypes::T_PUNCTUATION, '/'),
            new Token(TokenTypes::T_VARIABLE, 'foo'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_TEXT, 'bar'),
            new Token(TokenTypes::T_PUNCTUATION, '('),
            new Token(TokenTypes::T_QUOTED_STRING, 'baz'),
            new Token(TokenTypes::T_PUNCTUATION, ','),
            new Token(TokenTypes::T_PUNCTUATION, ')'),
            new Token(TokenTypes::T_PUNCTUATION, ')')
        ]);
        $expectedAst = new AstNode(AstNodeTypes::ROOT, null);
        $pathNode = new AstNode(AstNodeTypes::PATH, null);
        $expectedAst->addChild($pathNode);
        $variableConstraintNode = new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT, 'bar');
        $variableConstraintNode->addChild(new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT_PARAMETERS, ['baz']));
        $variableNode = new AstNode(AstNodeTypes::VARIABLE, 'foo');
        $variableNode->addChild($variableConstraintNode);
        $pathNode->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
        $pathNode->addChild($variableNode);
        $this->assertEquals($expectedAst, $this->parser->parse($tokens));
    }
}
