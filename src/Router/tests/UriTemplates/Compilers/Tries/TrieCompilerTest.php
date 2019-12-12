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
use Aphiria\Routing\UriTemplates\Rules\IRule;
use Aphiria\Routing\UriTemplates\Rules\IRuleFactory;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the trie compiler
 */
class TrieCompilerTest extends TestCase
{
    private TrieCompiler $compiler;
    /** @var IRuleFactory|MockObject */
    private IRuleFactory $ruleFactory;
    /** @var IUriTemplateParser|MockObject */
    private IUriTemplateParser $parser;
    /** @var IUriTemplateLexer|MockObject */
    private IUriTemplateLexer $lexer;
    private AstNode $ast;
    private RootTrieNode $expectedTrie;

    protected function setUp(): void
    {
        $this->ruleFactory = $this->createMock(IRuleFactory::class);
        $this->parser = $this->createMock(IUriTemplateParser::class);
        $this->lexer = $this->createMock(IUriTemplateLexer::class);
        $this->ast = new AstNode(AstNodeTypes::ROOT, null);
        $this->parser->method('parse')
            ->willReturn($this->ast);
        $this->compiler = new TrieCompiler($this->ruleFactory, $this->parser, $this->lexer);
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

    public function testCompilingPathVariableWithMultipleRulesAndParamsCreatesVariableNodeWithRules(): void
    {
        // Set up AST
        $rule1Node = (new AstNode(AstNodeTypes::VARIABLE_RULE, 'r1'))
            ->addChild(new AstNode(AstNodeTypes::VARIABLE_RULE_PARAMETERS, ['p1', 'p2']));
        $rule2Node = (new AstNode(AstNodeTypes::VARIABLE_RULE, 'r2'))
            ->addChild(new AstNode(AstNodeTypes::VARIABLE_RULE_PARAMETERS, ['p3', 'p4']));
        $variableNode = (new AstNode(AstNodeTypes::VARIABLE, 'foo'))
            ->addChild($rule1Node)
            ->addChild($rule2Node);
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild($variableNode);
        $this->ast->addChild($pathAst);

        // Set up rule factory
        /** @var IRule|MockObject $rule1 */
        $rule1 = $this->createMock(IRule::class);
        /** @var IRule|MockObject $rule2 */
        $rule2 = $this->createMock(IRule::class);
        $this->ruleFactory->expects($this->at(0))
            ->method('createRule')
            ->with('r1', ['p1', 'p2'])
            ->willReturn($rule1);
        $this->ruleFactory->expects($this->at(1))
            ->method('createRule')
            ->with('r2', ['p3', 'p4'])
            ->willReturn($rule2);

        // Test compiling
        $pathTemplate = '/:foo(r1(p1,p2),r2(p3,p4))';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new VariableTrieNode(
            new RouteVariable('foo', [$rule1, $rule2]),
            [],
            $expectedRoute
        ));
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->assertEquals($this->expectedTrie, $this->compiler->compile($expectedRoute));
    }

    public function testCompilingPathVariableWithNonRuleChildThrowsException(): void
    {
        $this->expectException(InvalidUriTemplateException::class);
        $ruleNode = (new AstNode(AstNodeTypes::VARIABLE_RULE, 'foo'))
            ->addChild(new AstNode(AstNodeTypes::TEXT, 'bar'));
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild($ruleNode);
        $this->ast->addChild($pathAst);
        $pathTemplate = '/:foo';
        $this->lexer->expects($this->once())
            ->method('lex')
            ->with($pathTemplate)
            ->willReturn(new TokenStream([]));
        $this->compiler->compile($this->createRoute($pathTemplate));
    }

    public function testCompilingPathVariableWithRulesCreatesVariableNodeWithRules(): void
    {
        // Set up AST
        $variableNode = (new AstNode(AstNodeTypes::VARIABLE, 'foo'))
            ->addChild(new AstNode(AstNodeTypes::VARIABLE_RULE, 'r1'));
        $pathAst = (new AstNode(AstNodeTypes::PATH, null))
            ->addChild(new AstNode(AstNodeTypes::SEGMENT_DELIMITER, '/'))
            ->addChild($variableNode);
        $this->ast->addChild($pathAst);

        // Set up rule factory
        /** @var IRule|MockObject $rule */
        $rule = $this->createMock(IRule::class);
        $this->ruleFactory->expects($this->at(0))
            ->method('createRule')
            ->with('r1')
            ->willReturn($rule);

        // Test compiling
        $pathTemplate = '/:foo(r1)';
        $expectedRoute = $this->createRoute($pathTemplate);
        $this->expectedTrie->addChild(new VariableTrieNode(
            new RouteVariable('foo', [$rule]),
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
