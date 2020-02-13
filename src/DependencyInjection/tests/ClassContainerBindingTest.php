<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\ClassContainerBinding;
use PHPUnit\Framework\TestCase;

/**
 * Tests the class container binding
 */
class ClassContainerBindingTest extends TestCase
{
    private ClassContainerBinding $binding;

    protected function setUp(): void
    {
        $this->binding = new ClassContainerBinding('foo', ['bar'], false);
    }

    public function testCheckingIfShouldResolveAsSingleton(): void
    {
        $singletonBinding = new ClassContainerBinding('foo', [], true);
        $prototypeBinding = new ClassContainerBinding('foo', [], false);
        $this->assertTrue($singletonBinding->resolveAsSingleton());
        $this->assertFalse($prototypeBinding->resolveAsSingleton());
    }

    public function testGettingConcreteClass(): void
    {
        $this->assertEquals('foo', $this->binding->getConcreteClass());
    }

    public function testGettingConstructorPrimitives(): void
    {
        $this->assertEquals(['bar'], $this->binding->getConstructorPrimitives());
    }
}
