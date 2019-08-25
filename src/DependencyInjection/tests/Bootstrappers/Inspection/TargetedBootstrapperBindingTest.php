<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests\Bootstrappers\Inspection;

use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\Inspection\TargetedBootstrapperBinding;
use Aphiria\DependencyInjection\IContainer;
use PHPUnit\Framework\TestCase;

/**
 * Tests the targeted bootstrapper binding
 */
class TargetedBootstrapperBindingTest extends TestCase
{
    public function testGettingPropertiesReturnsOneSetInConstructor(): void
    {
        $expectedBootstrapper = new class extends Bootstrapper {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $binding = new TargetedBootstrapperBinding('foo', 'bar', $expectedBootstrapper);
        $this->assertEquals('foo', $binding->getTargetClass());
        $this->assertEquals('bar', $binding->getInterface());
        $this->assertSame($expectedBootstrapper, $binding->getBootstrapper());
    }
}
