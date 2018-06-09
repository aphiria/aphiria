<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Regexes;

use Opulence\Routing\MethodRouteAction;
use Opulence\Routing\Regexes\Caching\IGroupRegexCache;
use Opulence\Routing\Regexes\GroupRegexCollection;
use Opulence\Routing\Regexes\GroupRegexFactory;
use Opulence\Routing\Route;
use Opulence\Routing\RouteCollection;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Tests the group regex factory
 */
class GroupRegexFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var GroupRegexFactory The factory to use in tests */
    private $regexFactory;
    /** @var RouteCollection The collection to use in the factory */
    private $routes;
    /** @var IGroupRegexCache|\PHPUnit_Framework_MockObject_MockObject The regex cache to use in the factory */
    private $regexCache;

    public function setUp(): void
    {
        $this->routes = new RouteCollection();
        $this->regexCache = $this->createMock(IGroupRegexCache::class);
        $this->regexFactory = new GroupRegexFactory($this->routes, $this->regexCache);
    }

    public function testBuildRegexesAreChunked(): void
    {
        $this->regexCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $regex1 = [];
        $regex2 = [];

        for ($i = 0;$i < 20;$i++) {
            $route = new Route('GET', new UriTemplate($i, false), new MethodRouteAction('class', 'method'));
            $this->routes->add($route);

            if ($i < 10) {
                $regex1[] = "($i)";
            } else {
                $regex2[] = "($i)";
            }
        }

        $regexes = $this->regexFactory->createRegexes();
        $regexChunk1 = $regexes->getByMethod('GET')[0];
        $regexChunk2 = $regexes->getByMethod('GET')[1];
        $this->assertEquals('#^(?:' . implode('|', $regex1) . ')$#', $regexChunk1->getGroupRegex());
        $this->assertEquals('#^(?:' . implode('|', $regex2) . ')$#', $regexChunk2->getGroupRegex());
    }

    public function testCacheHitReturnsCachedRegexes(): void
    {
        $regexes = new GroupRegexCollection();
        $this->regexCache->expects($this->once())
            ->method('get')
            ->willReturn($regexes);
        $this->assertSame($regexes, $this->regexFactory->createRegexes());
    }

    public function testCacheMissBuildsRegexes(): void
    {
        $this->regexCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $route1 = new Route('GET', new UriTemplate('foo', false), new MethodRouteAction('class', 'method'));
        $route2 = new Route('GET', new UriTemplate('bar', false), new MethodRouteAction('class', 'method'));
        $this->routes->addMany([$route1, $route2]);
        $regexes = $this->regexFactory->createRegexes();
        $regex = $regexes->getByMethod('GET')[0];
        $this->assertEquals('#^(?:(foo)|(bar))$#', $regex->getGroupRegex());
        $this->assertEquals([$route1, $route2], $regex->getRoutesByCapturingGroupOffsets());
    }

    public function testCapturingGroupOffsetsRespectRouteCapturingGroups(): void
    {
        $this->regexCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $route1 = new Route(
            'GET',
            new UriTemplate('(foo)', false, ['foo']),
            new MethodRouteAction('class', 'method')
        );
        $route2 = new Route(
            'GET',
            new UriTemplate('(bar)', false, ['bar']),
            new MethodRouteAction('class', 'method')
        );
        $this->routes->addMany([$route1, $route2]);
        $regexes = $this->regexFactory->createRegexes();
        $regex = $regexes->getByMethod('GET')[0];
        $this->assertEquals('#^(?:((foo))|((bar)))$#', $regex->getGroupRegex());
        $this->assertEquals([0 => $route1, 2 => $route2], $regex->getRoutesByCapturingGroupOffsets());
    }
}
