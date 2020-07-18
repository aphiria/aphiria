<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;
use Aphiria\DependencyInjection\Binders\Metadata\BoundInterface;
use Aphiria\DependencyInjection\Binders\Metadata\ResolvedInterface;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class BinderMetadataCollectionTest extends TestCase
{
    public function testBinderThatResolvesTargetedInterfaceIsNotReturnedForTargetedBoundInterfaceWithSameInterfaceButDifferentTarget(): void
    {
        $binderMetadatas = [
            new BinderMetadata($this->createMockBinder(), [], [new ResolvedInterface('foo', new TargetedContext('bar'))])
        ];
        $collection = new BinderMetadataCollection($binderMetadatas);
        $this->assertEmpty($collection->getBinderMetadataThatResolveInterface(new BoundInterface('foo', new TargetedContext('baz'))));
    }

    public function testBinderThatResolvesTargetedInterfaceIsReturnedForUniversalBoundInterfaceWithSameInterface(): void
    {
        $binderMetadatas = [
            new BinderMetadata($this->createMockBinder(), [], [new ResolvedInterface('foo', new TargetedContext('bar'))])
        ];
        $collection = new BinderMetadataCollection($binderMetadatas);
        $actualBinderMetadatas = $collection->getBinderMetadataThatResolveInterface(new BoundInterface('foo', new UniversalContext()));
        $this->assertCount(1, $actualBinderMetadatas);
        $this->assertSame($binderMetadatas[0], $actualBinderMetadatas[0]);
    }

    public function testBinderThatResolvesTargetedInterfaceIsReturnedForTargetedBoundInterfaceWithSameInterfaceAndTarget(): void
    {
        $binderMetadatas = [
            new BinderMetadata($this->createMockBinder(), [], [new ResolvedInterface('foo', new TargetedContext('bar'))])
        ];
        $collection = new BinderMetadataCollection($binderMetadatas);
        $actualBinderMetadatas = $collection->getBinderMetadataThatResolveInterface(new BoundInterface('foo', new TargetedContext('bar')));
        $this->assertCount(1, $actualBinderMetadatas);
        $this->assertSame($binderMetadatas[0], $actualBinderMetadatas[0]);
    }

    public function testBinderThatUniversallyResolvesInterfaceIsNotReturnedForUniversalBoundInterfaceWithDifferentInterface(): void
    {
        $binderMetadatas = [
            new BinderMetadata($this->createMockBinder(), [], [new ResolvedInterface('foo', new UniversalContext())])
        ];
        $collection = new BinderMetadataCollection($binderMetadatas);
        $actualBinderMetadatas = $collection->getBinderMetadataThatResolveInterface(new BoundInterface('bar', new UniversalContext()));
        $this->assertEmpty($actualBinderMetadatas);
    }

    public function testBinderThatUniversallyResolvesInterfaceIsReturnedForUniversalBoundInterfaceWithSameInterface(): void
    {
        $binderMetadatas = [
            new BinderMetadata($this->createMockBinder(), [], [new ResolvedInterface('foo', new UniversalContext())])
        ];
        $collection = new BinderMetadataCollection($binderMetadatas);
        $actualBinderMetadatas = $collection->getBinderMetadataThatResolveInterface(new BoundInterface('foo', new UniversalContext()));
        $this->assertCount(1, $actualBinderMetadatas);
        $this->assertSame($binderMetadatas[0], $actualBinderMetadatas[0]);
    }

    public function testBinderThatUniversallyResolvesInterfaceIsNotReturnedForTargetedBoundInterfaceWithSameInterface(): void
    {
        $binderMetadatas = [
            new BinderMetadata($this->createMockBinder(), [], [new ResolvedInterface('foo', new UniversalContext())])
        ];
        $collection = new BinderMetadataCollection($binderMetadatas);
        $actualBinderMetadatas = $collection->getBinderMetadataThatResolveInterface(new BoundInterface('foo', new TargetedContext('bar')));
        $this->assertEmpty($actualBinderMetadatas);
    }

    public function testGetAllBinderMetadataReturnsAllMetadata(): void
    {
        $expectedBinderMetadatas = [
            new BinderMetadata($this->createMockBinder(), [], []),
            new BinderMetadata($this->createMockBinder(), [], [])
        ];
        $collection = new BinderMetadataCollection($expectedBinderMetadatas);
        $this->assertSame($expectedBinderMetadatas, $collection->getAllBinderMetadata());
    }

    /**
     * Creates a mock binder
     *
     * @return Binder The binder
     */
    private function createMockBinder(): Binder
    {
        return new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
    }
}
