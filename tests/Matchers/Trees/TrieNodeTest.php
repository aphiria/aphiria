<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers\Trees;

use Aphiria\Routing\Matchers\Trees\LiteralTrieNode;
use Aphiria\Routing\Matchers\Trees\RouteVariable;
use Aphiria\Routing\Matchers\Trees\TrieNode;
use Aphiria\Routing\Matchers\Trees\VariableTrieNode;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Tests the trie node
 */
class TrieNodeTest extends TestCase
{
    /** @var TrieNode|MockObject */
    private TrieNode $node;

    protected function setUp(): void
    {
        $this->node = $this->createMockNode();
    }

    public function testAddingChildWithMoreLevelsAddsThemAll(): void
    {
        $fooRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $this->node->addChild(
            new LiteralTrieNode(
                'foo',
                [],
                $fooRoutes
            )
        );
        $barRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $bazRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $this->node->addChild(new LiteralTrieNode(
            'bar',
            [
                new LiteralTrieNode(
                    'baz',
                    [],
                    $bazRoutes
                )
            ],
            $barRoutes
        ));
        $expectedChildren = [
            new LiteralTrieNode(
                'foo',
                [],
                $fooRoutes
            ),
            new LiteralTrieNode(
                'bar',
                [
                    new LiteralTrieNode(
                        'baz',
                        [],
                        $bazRoutes
                    )
                ],
                $barRoutes
            )
        ];
        $this->assertEquals($expectedChildren, $this->node->getAllChildren());
    }

    public function testAddingChildThatHasLessLevelsStillItsChildren(): void
    {
        $fooRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $barRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $this->node->addChild(new LiteralTrieNode(
            'foo',
            [
                new LiteralTrieNode(
                    'bar',
                    [],
                    $barRoutes
                )
            ],
            $fooRoutes
        ));
        $bazRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $this->node->addChild(new LiteralTrieNode(
            'baz',
            [],
            $bazRoutes
        ));
        $expectedChildren = [
            new LiteralTrieNode(
                'foo',
                [
                    new LiteralTrieNode(
                        'bar',
                        [],
                        $barRoutes
                    )
                ],
                $fooRoutes
            ),
            new LiteralTrieNode(
                'baz',
                [],
                $bazRoutes
            )
        ];
        $this->assertEquals($expectedChildren, $this->node->getAllChildren());
    }

    public function testAddingLiteralChildNormalizesValueInMap(): void
    {
        $literalChild = new LiteralTrieNode('FOO', []);
        $this->node->addChild($literalChild);
        $this->assertSame($literalChild, $this->node->literalChildrenByValue['foo']);
    }

    public function testAddingLiteralChildWithDifferentValueAddsItAsSeparateChild(): void
    {
        $fooRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $barRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $this->node->addChild(new LiteralTrieNode(
            'foo',
            [],
            $fooRoutes
        ));
        $this->node->addChild(new LiteralTrieNode(
            'bar',
            [],
            $barRoutes
        ));
        $expectedChildren = [
            new LiteralTrieNode(
                'foo',
                [],
                $fooRoutes
            ),
            new LiteralTrieNode(
                'bar',
                [],
                $barRoutes
            )
        ];
        $this->assertEquals($expectedChildren, $this->node->getAllChildren());
    }

    public function testAddingVariableChildDifferingVariablePartsAddsItAsSeparateChild(): void
    {
        $fooRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $barRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $this->node->addChild(new VariableTrieNode(
            new RouteVariable('foo'),
            [],
            $fooRoutes
        ));
        $this->node->addChild(new VariableTrieNode(
            new RouteVariable('bar'),
            [],
            $barRoutes
        ));
        $expectedChildren = [
            new VariableTrieNode(
                new RouteVariable('foo'),
                [],
                $barRoutes
            ),
            new VariableTrieNode(
                new RouteVariable('bar'),
                [],
                $barRoutes
            )
        ];
        $this->assertEquals($expectedChildren, $this->node->getAllChildren());
    }

    public function testAddingChildrenWithSameTwoLevelsOfChildrenMergesRoutesOnThirdLevel(): void
    {
        $fooARoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $fooBRoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $barARoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $barBRoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $bazARoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $bazBRoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $this->node->addChild(new LiteralTrieNode(
            'foo',
            [
                new LiteralTrieNode(
                    'bar',
                    [
                        new LiteralTrieNode(
                            'baz',
                            [],
                            [$bazARoute]
                        )
                    ],
                    [$barARoute]
                )
            ],
            [$fooARoute]
        ));
        $this->node->addChild(new LiteralTrieNode(
            'foo',
            [
                new LiteralTrieNode(
                    'bar',
                    [
                        new LiteralTrieNode(
                            'baz',
                            [],
                            [$bazBRoute]
                        )
                    ],
                    [$barBRoute]
                )
            ],
            [$fooBRoute]
        ));
        $expectedChildren = [
            new LiteralTrieNode(
                'foo',
                [
                    new LiteralTrieNode(
                        'bar',
                        [
                            new LiteralTrieNode(
                                'baz',
                                [],
                                [$bazARoute, $bazBRoute]
                            )
                        ],
                        [$barARoute, $barBRoute]
                    )
                ],
                [$fooARoute, $fooBRoute]
            )
        ];
        $this->assertEquals($expectedChildren, $this->node->getAllChildren());
    }

    public function testAddingChildWithSameLiteralValueMergesRoutes(): void
    {
        $fooRoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $barRoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $this->node->addChild(new LiteralTrieNode(
            'foo',
            [],
            [$fooRoute]
        ));
        $this->node->addChild(new LiteralTrieNode(
            'foo',
            [],
            [$barRoute]
        ));
        $expectedChildren = [
            new LiteralTrieNode(
                'foo',
                [],
                [$fooRoute, $barRoute]
            )
        ];
        $this->assertEquals($expectedChildren, $this->node->getAllChildren());
    }

    public function testAddingChildWithSameVariablePartsMergesRoutes(): void
    {
        $fooRoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $barRoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $this->node->addChild(new VariableTrieNode(
            new RouteVariable('foo'),
            [],
            [$fooRoute]
        ));
        $this->node->addChild(new VariableTrieNode(
            new RouteVariable('foo'),
            [],
            [$barRoute]
        ));
        $expectedChildren = [
            new VariableTrieNode(
                new RouteVariable('foo'),
                [],
                [$fooRoute, $barRoute]
            )
        ];
        $this->assertEquals($expectedChildren, $this->node->getAllChildren());
    }

    public function testGettingAllChildrenReturnsLiteralThenVariableChildren(): void
    {
        $literalChild = new LiteralTrieNode('foo', []);
        $variableChild = new VariableTrieNode(new RouteVariable('foo'), []);
        $this->node->addChild($literalChild);
        $this->node->addChild($variableChild);
        $this->assertEquals([$literalChild, $variableChild], $this->node->getAllChildren());
    }

    /**
     * Creates a mock node for use in tests
     *
     * @return MockObject|TrieNode The mock node
     * @throws ReflectionException
     */
    private function createMockNode(): MockObject
    {
        return $this->getMockForAbstractClass(TrieNode::class, [], '', false);
    }
}
