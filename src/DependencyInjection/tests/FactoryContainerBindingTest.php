<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\FactoryContainerBinding;
use PHPUnit\Framework\TestCase;

class FactoryContainerBindingTest extends TestCase
{
    public function testCheckingIfResolvedAsSingleton(): void
    {
        $factory = fn () => null;
        $singletonFactory = new FactoryContainerBinding($factory, true);
        $this->assertTrue($singletonFactory->resolveAsSingleton());
        $prototypeFactory = new FactoryContainerBinding($factory, false);
        $this->assertFalse($prototypeFactory->resolveAsSingleton());
    }

    public function testGettingFactory(): void
    {
        $factory = fn () => null;
        $binding = new FactoryContainerBinding($factory, true);
        $this->assertSame($factory, $binding->getFactory());
    }
}
