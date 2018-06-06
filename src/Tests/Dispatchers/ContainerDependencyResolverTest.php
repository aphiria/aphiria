<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Dispatchers;

use Opulence\Api\Dispatchers\ContainerDependencyResolver;
use Opulence\Api\Dispatchers\DependencyResolutionException;
use Opulence\Ioc\IContainer;
use Opulence\Ioc\IocException;

/**
 * Tests the dependency resolver
 */
class ContainerDependencyResolverTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContainerDependencyResolver The dependency resolver to use in tests */
    private $dependencyResolver;
    /** @var IContainer|\PHPUnit_Framework_MockObject_MockObject The IoC container to use in tests */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->dependencyResolver = new ContainerDependencyResolver($this->container);
    }

    public function testContainerIsUsedToResolveDependencies(): void
    {
        $this->container->expects($this->once())
            ->method('resolve')
            ->with('foo')
            ->willReturn($this);
        $this->assertEquals($this, $this->dependencyResolver->resolve('foo'));
    }

    public function testIocExceptionsAreConverted(): void
    {
        $this->expectException(DependencyResolutionException::class);
        $this->container->expects($this->once())
            ->method('resolve')
            ->with('foo')
            ->willThrowException(new IocException('blah'));
        $this->dependencyResolver->resolve('foo');
    }
}
