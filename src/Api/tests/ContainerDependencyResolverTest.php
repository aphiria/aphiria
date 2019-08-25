<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

declare(strict_types=1);

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

namespace Aphiria\Api\Tests;

use Aphiria\Api\ContainerDependencyResolver;
use Aphiria\Api\DependencyResolutionException;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the dependency resolver
 */
class ContainerDependencyResolverTest extends TestCase
{
    private ContainerDependencyResolver $dependencyResolver;
    /** @var IContainer|MockObject */
    private IContainer $container;

    protected function setUp(): void
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

    public function testResolutionExceptionsAreConverted(): void
    {
        $this->container->expects($this->once())
            ->method('resolve')
            ->with('foo')
            ->willThrowException(new ResolutionException('interface', 'target', 'blah'));

        try {
            $this->dependencyResolver->resolve('foo');
            $this->fail('Failed to throw exception');
        } catch (DependencyResolutionException $ex) {
            $this->assertEquals('Could not resolve dependencies', $ex->getMessage());
            $this->assertEquals('interface', $ex->getInterface());
            $this->assertEquals('target', $ex->getTargetClass());
        }
    }
}
