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

use Aphiria\DependencyInjection\ClassContainerBinding;
use PHPUnit\Framework\TestCase;

class ClassContainerBindingTest extends TestCase
{
    public function testCheckingIfShouldResolveAsSingleton(): void
    {
        $singletonBinding = new ClassContainerBinding(self::class, [], true);
        $prototypeBinding = new ClassContainerBinding(self::class, [], false);
        $this->assertTrue($singletonBinding->resolveAsSingleton());
        $this->assertFalse($prototypeBinding->resolveAsSingleton());
    }

    public function testGettingConcreteClass(): void
    {
        $binding = new ClassContainerBinding(self::class, [], false);
        $this->assertSame(self::class, $binding->getConcreteClass());
    }

    public function testGettingConstructorPrimitives(): void
    {
        $binding = new ClassContainerBinding(self::class, ['bar'], false);
        $this->assertEquals(['bar'], $binding->getConstructorPrimitives());
    }
}
