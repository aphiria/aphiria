<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Binders\Inspection;

use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\Binders\Inspection\UniversalBinderBinding;
use Aphiria\DependencyInjection\IContainer;
use PHPUnit\Framework\TestCase;

/**
 * Tests the universal binder binding
 */
class UniversalBinderBindingTest extends TestCase
{
    public function testGettingPropertiesReturnsOneSetInConstructor(): void
    {
        $expectedBinder = new class extends Binder {
            public function bind(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binding = new UniversalBinderBinding('foo', $expectedBinder);
        $this->assertEquals('foo', $binding->getInterface());
        $this->assertSame($expectedBinder, $binding->getBinder());
    }
}
