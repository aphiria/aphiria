<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Trees;

use Opulence\Routing\Matchers\Trees\LiteralTrieNode;
use Opulence\Routing\Matchers\Trees\TrieNode;
use Opulence\Routing\Matchers\Trees\RouteVariable;
use Opulence\Routing\Matchers\Trees\VariableTrieNode;
use Opulence\Routing\Route;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the trie node
 */
class TrieNodeTest extends TestCase
{
    /** @var TrieNode|MockObject */
    private $node;

    public function setUp(): void
    {
        $this->node = $this->createMockNode();
    }

    public function testAddingChildWithMoreLevelsAddsThemAll(): void
    {
        $fooRoutes = [$this->createMock(Route::class)];
        $this->node->addChild(
            new LiteralTrieNode(
                'foo',
                [],
                $fooRoutes
            )
        );
        $barRoutes = [$this->createMock(Route::class)];
        $bazRoutes = [$this->createMock(Route::class)];
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
        $fooRoutes = [$this->createMock(Route::class)];
        $barRoutes = [$this->createMock(Route::class)];
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
        $bazRoutes = [$this->createMock(Route::class)];
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
        $fooRoutes = [$this->createMock(Route::class)];
        $barRoutes = [$this->createMock(Route::class)];
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
        $fooRoutes = [$this->createMock(Route::class)];
        $barRoutes = [$this->createMock(Route::class)];
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
        $fooARoute = $this->createMock(Route::class);
        $fooBRoute = $this->createMock(Route::class);
        $barARoute = $this->createMock(Route::class);
        $barBRoute = $this->createMock(Route::class);
        $bazARoute = $this->createMock(Route::class);
        $bazBRoute = $this->createMock(Route::class);
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
        $fooRoute = $this->createMock(Route::class);
        $barRoute = $this->createMock(Route::class);
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
        $fooRoute = $this->createMock(Route::class);
        $barRoute = $this->createMock(Route::class);
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
     * @throws \ReflectionException
     */
    private function createMockNode(): MockObject
    {
        return $this->getMockForAbstractClass(TrieNode::class, [], '', false);
    }
}
