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

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\Metadata\ImpossibleBindingException;
use Aphiria\DependencyInjection\IContainer;
use PHPUnit\Framework\TestCase;

class ImpossibleBindingExceptionTest extends TestCase
{
    public function testMultipleFailedBindersForSingleInterfaceAreFormattedCorrectly(): void
    {
        $binder1 = $this->createBinder();
        $binder2 = $this->createBinder();
        $exception = new ImpossibleBindingException(['foo' => [$binder1, $binder2]]);
        $this->assertEquals(
            'Impossible to resolve following interfaces: foo (attempted to be resolved in ' . \get_class($binder1) . ', ' . \get_class($binder2) . ')',
            $exception->getMessage()
        );
    }

    public function testMultipleFailedBindingsAreFormattedCorrectly(): void
    {
        $binder1 = $this->createBinder();
        $binder2 = $this->createBinder();
        $exception = new ImpossibleBindingException(['foo' => [$binder1], 'bar' => [$binder2]]);
        $this->assertEquals(
            'Impossible to resolve following interfaces: foo (attempted to be resolved in ' . \get_class($binder1) . '), bar (attempted to be resolved in ' . \get_class($binder2) . ')',
            $exception->getMessage()
        );
    }

    public function testSingleFailedBindingIsFormattedCorrectly(): void
    {
        $binder = $this->createBinder();
        $exception = new ImpossibleBindingException(['foo' => [$binder]]);
        $this->assertEquals(
            'Impossible to resolve following interfaces: foo (attempted to be resolved in ' . \get_class($binder) . ')',
            $exception->getMessage()
        );
    }

    /**
     * Creates a binder for use in tests
     *
     * @return Binder The binder
     */
    private function createBinder(): Binder
    {
        return new class() extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
    }
}
