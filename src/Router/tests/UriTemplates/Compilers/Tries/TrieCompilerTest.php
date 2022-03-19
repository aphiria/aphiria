<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\Compilers\Tries\LiteralTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RootTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RouteVariable;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieCompiler;
use Aphiria\Routing\UriTemplates\Compilers\Tries\VariableTrieNode;
use Aphiria\Routing\UriTemplates\Constraints\IntegerConstraint;
use Aphiria\Routing\UriTemplates\Constraints\IRouteVariableConstraint;
use Aphiria\Routing\UriTemplates\Constraints\RouteVariableConstraintFactory;
use Aphiria\Routing\UriTemplates\InvalidUriTemplateException;
use Aphiria\Routing\UriTemplates\Lexers\IUriTemplateLexer;
use Aphiria\Routing\UriTemplates\Lexers\LexingException;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeType;
use Aphiria\Routing\UriTemplates\Parsers\IUriTemplateParser;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TrieCompilerTest extends TestCase
{
    private TrieCompiler $compiler;
    private RouteVariableConstraintFactory $constraintFactory;
    private IUriTemplateParser&MockObject $parser;
    private IUriTemplateLexer&MockObject $lexer;
    private AstNode $ast;
    private RootTrieNode $expectedTrie;

    protected function setUp(): void
    {
        $this->constraintFactory = new RouteVariableConstraintFactory();
        $this->parser = $this->createMock(IUriTemplateParser::class);
        $this->lexer = $this->createMock(IUriTemplateLexer::class);
        $this->ast = new AstNode(AstNodeType::Root, null);
        $this->parser->method('parse')
            ->willReturn($this->ast);
        $this->compiler = new TrieCompiler($this->constraintFactory, $this->parser, $this->lexer);
        $this->expectedTrie = new RootTrieNode();
    }

    public function testCompilingEmptyPathPrependsWithSlash(): void
    {
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'));
        $this->ast->addChild($pathAst);
        $expectedRoute = $this->createRoute('');
        $this->expectedTrie->addChild(new LiteralTrieNode(
            '',
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with('/')
            ->willReturn(new TokenStream([]));
        $this->assertEquals(
            $this->expectedTrie,
            $this->compiler->compile($expectedRoute)
        );
    }

    public function testCompilingHostAddsTrieNodeToLastPathNodeAndOptionalNodes(): void
    {
        $hostAst = (new AstNode(AstNodeType::Host, null))
            ->addChild(new AstNode(AstNodeType::Text, 'example'))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '.'))
            ->addChild(new AstNode(AstNodeType::Text, 'com'));
        $this->ast->addChild($hostAst);
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild(new AstNode(AstNodeType::Text, 'foo'))
            ->addChild(
                (new AstNode(AstNodeType::OptionalRoutePart, '['))
                    ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
                    ->addChild(new AstNode(AstNodeType::Text, 'bar'))
            );
        $this->ast->addChild($pathAst);
        $hostTemplate = 'example.com';
        $pathTemplate = '/foo[/bar]';
        $expectedRoute = $this->createRoute($pathTemplate, $hostTemplate);
        $expectedHostTrie = new RootTrieNode([
            new LiteralTrieNode(
                'com',
                [
                    new LiteralTrieNode(
                        'example',
                        [],
                        $expectedRoute
                    )
                ]
            )
        ]);
        $this->expectedTrie->addChild(new LiteralTrieNode(
            'foo',
            [
                new LiteralTrieNode('bar', [], [], $expectedHostTrie)
            ],
            [],
            $expectedHostTrie
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($hostTemplate . $pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testCompilingHostWithOptionalPartAddsRouteToItAndLastNonOptionalHostPart(): void
    {
        $hostAst = (new AstNode(AstNodeType::Host, null))
            ->addChild(
                (new AstNode(AstNodeType::OptionalRoutePart, '['))
                    ->addChild(new AstNode(AstNodeType::Text, 'api'))
                    ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '.'))
            )
            ->addChild(new AstNode(AstNodeType::Text, 'example'))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '.'))
            ->addChild(new AstNode(AstNodeType::Text, 'com'));
        $this->ast->addChild($hostAst);
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild(new AstNode(AstNodeType::Text, 'foo'));
        $this->ast->addChild($pathAst);
        $hostTemplate = 'example.com';
        $pathTemplate = '/foo';
        $expectedRoute = $this->createRoute($pathTemplate, $hostTemplate);
        $this->expectedTrie->addChild(new LiteralTrieNode(
            'foo',
            [],
            [],
            new RootTrieNode([
                new LiteralTrieNode(
                    'com',
                    [
                        new LiteralTrieNode(
                            'example',
                            [
                                new LiteralTrieNode(
                                    'api',
                                    [],
                                    $expectedRoute
                                )
                            ],
                            $expectedRoute
                        )
                    ]
                )
            ])
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($hostTemplate . $pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testCompilingInvalidVariableNodeThrowsException(): void
    {
        $this->expectException(InvalidUriTemplateException::class);
        $this->expectExceptionMessage('Unexpected node type ' . AstNodeType::Path->name);
        $variableNode = new AstNode(AstNodeType::Variable, 'foo');
        // Add an invalid child to the variable node
        $variableNode->addChild(new AstNode(AstNodeType::Path, null));
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild($variableNode);
        $this->ast->addChild($pathAst);
        $pathTemplate = '/:foo';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new VariableTrieNode(
            new RouteVariable('foo'),
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->compiler->compile($expectedRoute);
    }

    public function testCompilingPathVariableCreatesVariableNodeWithRouteVariable(): void
    {
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild(new AstNode(AstNodeType::Variable, 'foo'));
        $this->ast->addChild($pathAst);
        $pathTemplate = '/:foo';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new VariableTrieNode(
            new RouteVariable('foo'),
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testCompilingPathVariableWithMultipleConstraintsAndParamsCreatesVariableNodeWithConstraints(): void
    {
        // Set up AST
        $constraint1Node = (new AstNode(AstNodeType::VariableConstraint, 'r1'))
            ->addChild(new AstNode(AstNodeType::VariableConstraintParameters, ['p1', 'p2']));
        $constraint2Node = (new AstNode(AstNodeType::VariableConstraint, 'r2'))
            ->addChild(new AstNode(AstNodeType::VariableConstraintParameters, ['p3', 'p4']));
        $variableNode = (new AstNode(AstNodeType::Variable, 'foo'))
            ->addChild($constraint1Node)
            ->addChild($constraint2Node);
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild($variableNode);
        $this->ast->addChild($pathAst);

        // Set up constraint factory
        /** @var IRouteVariableConstraint&MockObject $constraint1 */
        $constraint1 = $this->createMock(IRouteVariableConstraint::class);
        /** @var IRouteVariableConstraint&MockObject $constraint2 */
        $constraint2 = $this->createMock(IRouteVariableConstraint::class);
        $this->constraintFactory->registerConstraintFactory('r1', fn (string $p1, string $p2) => $constraint1);
        $this->constraintFactory->registerConstraintFactory('r2', fn (string $p1, string $p2) => $constraint1);

        // Test compiling
        $pathTemplate = '/:foo(r1(p1,p2),r2(p3,p4))';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new VariableTrieNode(
            new RouteVariable('foo', [$constraint1, $constraint2]),
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testCompilingPathVariableWithNonConstraintChildThrowsException(): void
    {
        $this->expectException(InvalidUriTemplateException::class);
        $constraintNode = (new AstNode(AstNodeType::VariableConstraint, 'foo'))
            ->addChild(new AstNode(AstNodeType::Text, 'bar'));
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild($constraintNode);
        $this->ast->addChild($pathAst);
        $pathTemplate = '/:foo';
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->compiler->compile($this->createRoute($pathTemplate));
    }

    public function testCompilingPathVariableWithConstraintsCreatesVariableNodeWithConstraints(): void
    {
        // Set up AST
        $variableNode = (new AstNode(AstNodeType::Variable, 'foo'))
            ->addChild(new AstNode(AstNodeType::VariableConstraint, 'r1'));
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild($variableNode);
        $this->ast->addChild($pathAst);

        // Set up constraint factory
        /** @var IRouteVariableConstraint&MockObject $constraint */
        $constraint = $this->createMock(IRouteVariableConstraint::class);
        $this->constraintFactory->registerConstraintFactory('r1', fn () => $constraint);

        // Test compiling
        $pathTemplate = '/:foo(r1)';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new VariableTrieNode(
            new RouteVariable('foo', [$constraint]),
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testCompilingRequiredAndOptionalPathSegmentsCreatesNodesWithSameRoute(): void
    {
        $optionalNode = (new AstNode(AstNodeType::OptionalRoutePart))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild(new AstNode(AstNodeType::Text, 'bar'));
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild(new AstNode(AstNodeType::Text, 'foo'))
            ->addChild($optionalNode);
        $this->ast->addChild($pathAst);
        $pathTemplate = '/foo[/bar]';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new LiteralTrieNode(
            'foo',
            [
                new LiteralTrieNode(
                    'bar',
                    [],
                    $expectedRoute
                )
            ],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testCompilingTextOnlyPathAstAddsRouteToNode(): void
    {
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild(new AstNode(AstNodeType::Text, 'foo'));
        $this->ast->addChild($pathAst);
        $pathTemplate = '/foo';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new LiteralTrieNode(
            'foo',
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testCompilingVariableOnlyPathAstAddsRouteToNode(): void
    {
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild(new AstNode(AstNodeType::Variable, 'foo'));
        $this->ast->addChild($pathAst);
        $pathTemplate = '/:foo';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new VariableTrieNode(
            new RouteVariable('foo'),
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testConstructingCompilerWithoutConstraintsStillRegistersDefaultOnes(): void
    {
        // Set up AST
        $variableNode = (new AstNode(AstNodeType::Variable, 'foo'))
            ->addChild(new AstNode(AstNodeType::VariableConstraint, 'int'));
        $pathAst = (new AstNode(AstNodeType::Path, null))
            ->addChild(new AstNode(AstNodeType::SegmentDelimiter, '/'))
            ->addChild($variableNode);
        $this->ast->addChild($pathAst);

        // Test compiling
        $pathTemplate = '/:foo(int)';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new VariableTrieNode(
            new RouteVariable('foo', [new IntegerConstraint()]),
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $compiler = new TrieCompiler(null, $this->parser, $this->lexer);
        $this->assertEquals($this->expectedTrie, $compiler->compile($expectedRoute));
    }

    public function testLexingExceptionGetsRethrown(): void
    {
        $this->expectException(InvalidUriTemplateException::class);
        $this->expectExceptionMessage('URI template could not be compiled');
        $this->lexer->method('lex')
            ->willThrowException(new LexingException());
        $this->compiler->compile($this->createRoute('/path'));
    }

    public function testUnexpectedTokenExceptionGetsRethrown(): void
    {
        $this->expectException(InvalidUriTemplateException::class);
        $this->expectExceptionMessage('URI template could not be compiled');
        $tokens = new TokenStream([]);
        $this->lexer->method('lex')
            ->willReturn($tokens);
        $this->parser->method('parse')
            ->with($tokens)
            ->willThrowException(new UnexpectedTokenException());
        $this->compiler->compile($this->createRoute('/path'));
    }

    /**
     * Creates routes for use in tests
     *
     * @param string $pathTemplate The path template for the route
     * @param string|null $hostTemplate The host template for the route
     * @return Route The route
     */
    private function createRoute(string $pathTemplate, string $hostTemplate = null): Route
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };

        return new Route(new UriTemplate($pathTemplate, $hostTemplate), new RouteAction($controller::class, 'bar'), []);
    }
}
