<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
        $exception = new ImpossibleBindingException([self::class => [$binder1, $binder2]]);
        $this->assertSame(
            'Impossible to resolve following interfaces: ' . self::class . ' (attempted to be resolved in ' . $binder1::class . ', ' . $binder2::class . ')',
            $exception->getMessage()
        );
    }

    public function testMultipleFailedBindingsAreFormattedCorrectly(): void
    {
        $interface1 = new class() {
        };
        $interface2 = new class() {
        };
        $binder1 = $this->createBinder();
        $binder2 = $this->createBinder();
        $exception = new ImpossibleBindingException([$interface1::class => [$binder1], $interface2::class => [$binder2]]);
        $this->assertSame(
            'Impossible to resolve following interfaces: ' . $interface1::class . ' (attempted to be resolved in ' . $binder1::class . '), ' . $interface2::class . ' (attempted to be resolved in ' . $binder2::class . ')',
            $exception->getMessage()
        );
    }

    public function testSingleFailedBindingIsFormattedCorrectly(): void
    {
        $binder = $this->createBinder();
        $exception = new ImpossibleBindingException([self::class => [$binder]]);
        $this->assertSame(
            'Impossible to resolve following interfaces: ' . self::class . ' (attempted to be resolved in ' . $binder::class . ')',
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
