<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\FactoryContainerBinding;
use PHPUnit\Framework\TestCase;

class FactoryContainerBindingTest extends TestCase
{
    public function testCheckingIfResolvedAsSingleton(): void
    {
        $factory = fn (): object => $this;
        $singletonFactory = new FactoryContainerBinding($factory, true);
        $this->assertTrue($singletonFactory->resolveAsSingleton);
        $prototypeFactory = new FactoryContainerBinding($factory, false);
        $this->assertFalse($prototypeFactory->resolveAsSingleton);
    }

    public function testGettingFactory(): void
    {
        $factory = fn (): object => $this;
        $binding = new FactoryContainerBinding($factory, true);
        $this->assertSame($factory, $binding->factory);
    }
}
