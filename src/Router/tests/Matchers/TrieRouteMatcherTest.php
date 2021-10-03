<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers;

use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Matchers\TrieRouteMatcher;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\Compilers\Tries\LiteralTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RootTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RouteVariable;
use Aphiria\Routing\UriTemplates\Compilers\Tries\VariableTrieNode;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

class TrieRouteMatcherTest extends TestCase
{
    private TrieRouteMatcher $matcher;
    private RootTrieNode $rootNode;

    protected function setUp(): void
    {
        $this->rootNode = new RootTrieNode();
        $this->matcher = new TrieRouteMatcher($this->rootNode);
    }

    public function testFailedHttpMethodConstraintsSetAllowedMethodsInResult(): void
    {
        $controller = new class () {
            public function bar1(): void
            {
            }

            public function bar2(): void
            {
            }
        };
        $routes = [
            new Route(
                new UriTemplate('foo'),
                new RouteAction($controller::class, 'bar1'),
                [new HttpMethodRouteConstraint('GET')]
            ),
            new Route(
                new UriTemplate('foo'),
                new RouteAction($controller::class, 'bar2'),
                [new HttpMethodRouteConstraint('POST')]
            )
        ];
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            $routes
        ));
        $matchingResult = $this->matcher->matchRoute('DELETE', '', 'foo');
        $this->assertFalse($matchingResult->matchFound);
        $this->assertEquals(['GET', 'HEAD', 'POST'], $matchingResult->allowedMethods);
        $this->assertNull($matchingResult->route);
    }

    public function testLiteralMatchWithDifferingCaseThanWhatIsRegisteredStillMatches(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(
            new UriTemplate('foo'),
            new RouteAction($controller::class, 'bar'),
            [new HttpMethodRouteConstraint('GET')]
        );
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            $expectedRoute
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'FOO');
        $this->assertTrue($matchingResult->matchFound);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testLiteralSegmentIsMatchedEvenIfRegisteredAfterMatchingRouteWithVariableSegment(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(
            new UriTemplate('foo'),
            new RouteAction($controller::class, 'bar'),
            [new HttpMethodRouteConstraint('GET')]
        );
        $this->rootNode->addChild(new VariableTrieNode(
            'var',
            [],
            new Route(
                new UriTemplate(':var'),
                new RouteAction($controller::class, 'bar'),
                [new HttpMethodRouteConstraint('GET')]
            )
        ));
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            $expectedRoute
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'foo');
        $this->assertTrue($matchingResult->matchFound);
        $this->assertEmpty($matchingResult->routeVariables);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testMatchingEmptyPathWithEmptyPathRouteReturnsMatch(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(
            new UriTemplate(''),
            new RouteAction($controller::class, 'bar'),
            [new HttpMethodRouteConstraint('GET')]
        );
        $this->rootNode->addChild(new LiteralTrieNode(
            '',
            [],
            $expectedRoute
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', '');
        $this->assertTrue($matchingResult->matchFound);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testMatchingHostWithLiteralMatchReturnsExpectedResult(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(
            new UriTemplate(''),
            new RouteAction($controller::class, 'bar'),
            [new HttpMethodRouteConstraint('GET')]
        );
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            [],
            new RootTrieNode([
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
            ])
        ));
        $matchingResult = $this->matcher->matchRoute('GET', 'example.com', 'foo');
        $this->assertTrue($matchingResult->matchFound);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testMatchingHostWithVariableAddsVariableToResult(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(
            new UriTemplate(''),
            new RouteAction($controller::class, 'bar'),
            [new HttpMethodRouteConstraint('GET')]
        );
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            [],
            new RootTrieNode([
                new LiteralTrieNode(
                    'com',
                    [
                        new VariableTrieNode(
                            new RouteVariable('domain'),
                            [],
                            $expectedRoute
                        )
                    ]
                )
            ])
        ));
        $matchingResult = $this->matcher->matchRoute('GET', 'example.com', 'foo');
        $this->assertTrue($matchingResult->matchFound);
        $this->assertEquals(['domain' => 'example'], $matchingResult->routeVariables);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testMatchingRouteChecksConstraints(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $constraint = $this->createMock(IRouteConstraint::class);
        $constraint->expects($this->once())
            ->method('passes')
            ->with($this->anything(), 'GET', '', 'foo', [])
            ->willReturn(true);
        $expectedRoute = new Route(new UriTemplate('foo'), new RouteAction($controller::class, 'bar'), [$constraint]);
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            $expectedRoute
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'foo');
        $this->assertTrue($matchingResult->matchFound);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testMatchingRouteWithFailingConstraintReturnsUnsuccessfulResult(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $constraint = $this->createMock(IRouteConstraint::class);
        $constraint->expects($this->once())
            ->method('passes')
            ->with($this->anything(), 'GET', '', 'foo', [])
            ->willReturn(false);
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            new Route(new UriTemplate('foo'), new RouteAction($controller::class, 'bar'), [$constraint])
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'foo');
        $this->assertFalse($matchingResult->matchFound);
        $this->assertNull($matchingResult->route);
    }

    public function testMatchingWithLeadingAndTrailingSlashesDoesNotMatter(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(
            new UriTemplate('foo'),
            new RouteAction($controller::class, 'bar'),
            [new HttpMethodRouteConstraint('GET')]
        );
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            $expectedRoute
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', '/foo/');
        $this->assertTrue($matchingResult->matchFound);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testMatchWithoutRoutesIsNotReturned(): void
    {
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            []
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'foo');
        $this->assertFalse($matchingResult->matchFound);
        $this->assertNull($matchingResult->route);
    }

    public function testNoMatchingRouteReturnsNull(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->rootNode->addChild(new LiteralTrieNode(
            'foo',
            [],
            new Route(
                new UriTemplate('foo'),
                new RouteAction($controller::class, 'bar'),
                [new HttpMethodRouteConstraint('GET')]
            )
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'bar');
        $this->assertFalse($matchingResult->matchFound);
        $this->assertNull($matchingResult->route);
    }

    public function testRouteVariableIsSetFromMatchingVariableNodes(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $constraint = new HttpMethodRouteConstraint('GET');
        $expectedRoute = new Route(new UriTemplate(':var1/:var2'), new RouteAction($controller::class, 'bar'), [$constraint]);
        $this->rootNode->addChild(new VariableTrieNode(
            new RouteVariable('var1'),
            [
                new VariableTrieNode(
                    new RouteVariable('var2'),
                    [],
                    $expectedRoute
                )
            ],
            []
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'val1/val2');
        $this->assertEquals(['var1' => 'val1', 'var2' => 'val2'], $matchingResult->routeVariables);
        $this->assertTrue($matchingResult->matchFound);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testVariableValuesFromNodesWhoseChildNodesDidNotMatchAreNotIncludedInMatchingRoute(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        // Note: Purposely registering two separate variable nodes rather than two routes under one node
        $this->rootNode->addChild(new VariableTrieNode(
            new RouteVariable('var1'),
            [
                new LiteralTrieNode(
                    'foo',
                    [],
                    new Route(
                        new UriTemplate(':var1/foo'),
                        new RouteAction($controller::class, 'bar'),
                        [new HttpMethodRouteConstraint('GET')]
                    )
                )
            ]
        ));
        $expectedRoute = new Route(
            new UriTemplate(':var2/bar'),
            new RouteAction($controller::class, 'bar'),
            [new HttpMethodRouteConstraint('GET')]
        );
        $this->rootNode->addChild(new VariableTrieNode(
            new RouteVariable('var2'),
            [
                new LiteralTrieNode(
                    'bar',
                    [],
                    $expectedRoute
                )
            ]
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'val1/bar');
        $this->assertEquals(['var2' => 'val1'], $matchingResult->routeVariables);
        $this->assertTrue($matchingResult->matchFound);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }

    public function testVariableValuesFromRoutesAreWithFailedConstraintsNotIncludedInMatchingRoute(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        // Note: Purposely registering two separate variable nodes rather than two routes under one node
        $failingConstraint = $this->createMock(IRouteConstraint::class);
        $failingConstraint->expects($this->once())
            ->method('passes')
            ->willReturn(false);
        $this->rootNode->addChild(new VariableTrieNode(
            new RouteVariable('var1'),
            [],
            new Route(new UriTemplate(':var1'), new RouteAction($controller::class, 'bar'), [$failingConstraint])
        ));
        $passingConstraint = $this->createMock(IRouteConstraint::class);
        $passingConstraint->expects($this->once())
            ->method('passes')
            ->willReturn(true);
        $expectedRoute = new Route(new UriTemplate(':var2'), new RouteAction($controller::class, 'bar'), [$passingConstraint]);
        $this->rootNode->addChild(new VariableTrieNode(
            new RouteVariable('var2'),
            [],
            $expectedRoute
        ));
        $matchingResult = $this->matcher->matchRoute('GET', '', 'foo');
        $this->assertEquals(['var2' => 'foo'], $matchingResult->routeVariables);
        $this->assertTrue($matchingResult->matchFound);
        $this->assertSame($expectedRoute, $matchingResult->route);
    }
}
