<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\Compilers\Tries\LiteralTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RouteVariable;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\VariableTrieNode;
use Aphiria\Routing\UriTemplates\UriTemplate;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TrieNodeTest extends TestCase
{
    private TrieNode|MockObject $node;

    protected function setUp(): void
    {
        $this->node = $this->createMockNode();
    }

    public function testAddingChildWithMoreLevelsAddsThemAll(): void
    {
        $fooRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
        $this->node->addChild(
            new LiteralTrieNode(
                'foo',
                [],
                $fooRoutes
            )
        );
        $barRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
        $bazRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
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
        $fooRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
        $barRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
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
        $bazRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
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

    public function testAddingInvalidChildNodeThrowsException(): void
    {
        $invalidChildNode = new class() extends TrieNode {
            public function __construct()
            {
                parent::__construct([], [], null);
            }
        };
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unexpected trie node type ' . $invalidChildNode::class);
        $this->node->addChild($invalidChildNode);
    }

    public function testAddingLiteralChildNormalizesValueInMap(): void
    {
        $literalChild = new LiteralTrieNode('FOO', []);
        $this->node->addChild($literalChild);
        $this->assertSame($literalChild, $this->node->literalChildrenByValue['foo']);
    }

    public function testAddingLiteralChildWithDifferentValueAddsItAsSeparateChild(): void
    {
        $fooRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
        $barRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
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
        $fooRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
        $barRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
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
        $fooARoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $fooBRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $barARoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $barBRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $bazARoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $bazBRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
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
        $fooRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $barRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
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
        $fooRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'foo'), []);
        $barRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $bazRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'baz'), []);
        $this->node->addChild(new VariableTrieNode(
            new RouteVariable('foo'),
            [],
            [$fooRoute]
        ));
        $this->node->addChild(new VariableTrieNode(
            new RouteVariable('foo'),
            // Test that grandchildren also get merged in
            [
                new LiteralTrieNode('bar', [], [$bazRoute])
            ],
            [$barRoute]
        ));
        $expectedChildren = [
            new VariableTrieNode(
                new RouteVariable('foo'),
                [
                    new LiteralTrieNode('bar', [], [$bazRoute])
                ],
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
     * @return TrieNode|MockObject The mock node
     */
    private function createMockNode(): TrieNode|MockObject
    {
        return $this->getMockForAbstractClass(TrieNode::class, [], '', false);
    }
}
