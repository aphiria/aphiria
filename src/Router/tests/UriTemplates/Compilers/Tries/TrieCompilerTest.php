<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Compilers\Tries\Compilers;

use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\InvalidUriTemplateException;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieCompiler;
use Aphiria\Routing\UriTemplates\Compilers\Tries\LiteralTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RootTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RouteVariable;
use Aphiria\Routing\UriTemplates\Compilers\Tries\VariableTrieNode;
use Aphiria\Routing\UriTemplates\Lexers\IUriTemplateLexer;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeTypes;
use Aphiria\Routing\UriTemplates\Parsers\IUriTemplateParser;
use Aphiria\Routing\UriTemplates\Constraints\IRouteVariableConstraint;
use Aphiria\Routing\UriTemplates\Constraints\IRouteVariableConstraintFactory;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the trie compiler
 */
class TrieCompilerTest extends TestCase
{
    private TrieCompiler $compiler;
    /** @var IRouteVariableConstraintFactory|MockObject */
    private IRouteVariableConstraintFactory $constraintFactory;
    /** @var IUriTemplateParser|MockObject */
    private IUriTemplateParser $parser;
    /** @var IUriTemplateLexer|MockObject */
    private IUriTemplateLexer $lexer;
    private AstNode $ast;
    private RootTrieNode $expectedTrie;

    protected function setUp(): void
    {
        $this->constraintFactory = $this->createMock(IRouteVariableConstraintFactory::class);
        $this->parser = $this->createMock(IUriTemplateParser::class);
        $this->lexer = $this->createMock(IUriTemplateLexer::class);
        $this->ast = new AstNode(AstNodeTypes::ROOT, null);
        $this->parser->method('parse')
            ->willReturn($this->ast);
        $this->compiler = new TrieCompiler($this->constraintFactory, $this->parser, $this->lexer);
        $this->expectedTrie = new RootTrieNode();
    }

    public function testCompilingEmptyPathPrependsWithSlash(): void
    {
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'));
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
        $hostAst = (new AstNode(AstNodeTypes::HOST, null))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'example'))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '.'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'com'));
        $this->ast->addChild($hostAst);
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'))
            ->addChild(
                (new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART, '['))
                ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
                ->addChild(new AstNode(AstNodeTypes::TEXT, 'bar'))
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
        $hostAst = (new AstNode(AstNodeTypes::HOST, null))
            ->addChild(
                (new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART, '['))
                ->addChild(new AstNode(AstNodeTypes::TEXT, 'api'))
                ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '.'))
            )
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'example'))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '.'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'com'));
        $this->ast->addChild($hostAst);
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'));
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

    public function testCompilingRequiredAndOptionalPathSegmentsCreatesNodesWithSameRoute(): void
    {
        $optionalNode = (new AstNode(AstNodeTypes::OPTIONAL_ROUTE_PART))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'bar'));
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'))
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
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'foo'));
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
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild(new AstNode(AstNodeTypes::VARIABLE, 'foo'));
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

    public function testCompilingPathVariableCreatesVariableNodeWithRouteVariable(): void
    {
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild(new AstNode(AstNodeTypes::VARIABLE, 'foo'));
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
        $constraint1Node = (new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT, 'r1'))
            ->addChild(new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT_PARAMETERS, ['p1', 'p2']));
        $constraint2Node = (new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT, 'r2'))
            ->addChild(new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT_PARAMETERS, ['p3', 'p4']));
        $variableNode = (new AstNode(AstNodeTypes::VARIABLE, 'foo'))
            ->addChild($constraint1Node)
            ->addChild($constraint2Node);
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild($variableNode);
        $this->ast->addChild($pathAst);

        // Set up constraint factory
        /** @var IRouteVariableConstraint|MockObject $constraint1 */
        $constraint1 = $this->createMock(IRouteVariableConstraint::class);
        /** @var IRouteVariableConstraint|MockObject $constraint2 */
        $constraint2 = $this->createMock(IRouteVariableConstraint::class);
        $this->constraintFactory->expects($this->at(0))
            ->method('createConstraint')
            ->with('r1', ['p1', 'p2'])
            ->willReturn($constraint1);
        $this->constraintFactory->expects($this->at(1))
            ->method('createConstraint')
            ->with('r2', ['p3', 'p4'])
            ->willReturn($constraint2);

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
        $constraintNode = (new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT, 'foo'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'bar'));
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
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
        $variableNode = (new AstNode(AstNodeTypes::VARIABLE, 'foo'))
            ->addChild(new AstNode(AstNodeTypes::VARIABLE_CONSTRAINT, 'r1'));
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild($variableNode);
        $this->ast->addChild($pathAst);

        // Set up constraint factory
        /** @var IRouteVariableConstraint|MockObject $constraint */
        $constraint = $this->createMock(IRouteVariableConstraint::class);
        $this->constraintFactory->expects($this->at(0))
            ->method('createConstraint')
            ->with('r1')
            ->willReturn($constraint);

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

    /**
     * Creates routes for use in tests
     *
     * @param string $pathTemplate The path template for the route
     * @param string|null $hostTemplate The host template for the route
     * @return Route The route
     */
    private function createRoute(string $pathTemplate, string $hostTemplate = null): Route
    {
        return new Route(new UriTemplate($pathTemplate, $hostTemplate), new MethodRouteAction('Foo', 'bar'), []);
    }
}
