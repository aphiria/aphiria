<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Metadata;

use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadata;
use Aphiria\DependencyInjection\Binders\Metadata\FailedBinderMetadataCollectionException;
use Aphiria\DependencyInjection\Binders\Metadata\ResolvedInterface;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\Tests\Binders\Metadata\Mocks\IFoo;
use Aphiria\DependencyInjection\Tests\Binders\Mocks\Binder;
use Aphiria\DependencyInjection\UniversalContext;
use PHPUnit\Framework\TestCase;

class FailedBinderMetadataCollectionExceptionTest extends TestCase
{
    public function testPropertiesAreSet(): void
    {
        $binder = new class() extends Binder {
            public function bind(IContainer $container): void
            {
                $container->resolve(IFoo::class);
            }
        };
        $binderMetadata = new BinderMetadata($binder, [], [new ResolvedInterface(IFoo::class, new UniversalContext())]);
        $exception = new FailedBinderMetadataCollectionException($binderMetadata, IFoo::class);
        $this->assertEquals('Failed to collect metadata for ' . \get_class($binder), $exception->getMessage());
        $this->assertSame($binderMetadata, $exception->getIncompleteBinderMetadata());
        $this->assertEquals(IFoo::class, $exception->getFailedInterface());
    }
}
