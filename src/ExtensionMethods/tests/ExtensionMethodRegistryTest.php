<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ExtensionMethods\Tests;

use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Aphiria\ExtensionMethods\Tests\Mocks\BaseBar;
use Aphiria\ExtensionMethods\Tests\Mocks\BaseFoo;
use Aphiria\ExtensionMethods\Tests\Mocks\IBar;
use Aphiria\ExtensionMethods\Tests\Mocks\IFoo;
use PHPUnit\Framework\TestCase;

class ExtensionMethodRegistryTest extends TestCase
{
    public function testGettingExtensionMethodMultipleTimesUsesMemoizedClosure(): void
    {
        // Note: This is mainly done for code coverage purposes
        $foo = new class() {
        };
        $expectedExtensionMethod = fn (): string => 'foo';
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', $expectedExtensionMethod);
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
    }

    public function testGettingExtensionMethodOnChildClassOfRegisteredBaseClassStillWorks(): void
    {
        $foo = new class() extends BaseFoo {
        };
        $expectedExtensionMethod = fn (): string => 'foo';
        ExtensionMethodRegistry::registerExtensionMethod(BaseFoo::class, 'foo', $expectedExtensionMethod);
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
    }

    public function testGettingExtensionMethodOnChildClassOfRegisteredInterfaceStillWorks(): void
    {
        $foo = new class() implements IFoo {
        };
        $expectedExtensionMethod = fn (): string => 'foo';
        ExtensionMethodRegistry::registerExtensionMethod(IFoo::class, 'foo', $expectedExtensionMethod);
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
    }

    public function testGettingExtensionMethodOnNestedChildClassOfRegisteredBaseClassesStillWorks(): void
    {
        $foo = new class() extends BaseBar {
        };
        $expectedExtensionMethod1 = fn (): string => 'foo';
        $expectedExtensionMethod2 = fn (): string => 'bar';
        ExtensionMethodRegistry::registerExtensionMethod(BaseFoo::class, 'foo', $expectedExtensionMethod1);
        ExtensionMethodRegistry::registerExtensionMethod(BaseBar::class, 'bar', $expectedExtensionMethod2);
        $this->assertSame($expectedExtensionMethod1, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
        $this->assertSame($expectedExtensionMethod2, ExtensionMethodRegistry::getExtensionMethod($foo, 'bar'));
    }

    public function testGettingExtensionMethodOnNestedChildClassOfRegisteredInterfaceStillWorks(): void
    {
        $foo = new class() implements IBar {
        };
        $expectedExtensionMethod1 = fn (): string => 'foo';
        $expectedExtensionMethod2 = fn (): string => 'bar';
        ExtensionMethodRegistry::registerExtensionMethod(IFoo::class, 'foo', $expectedExtensionMethod1);
        ExtensionMethodRegistry::registerExtensionMethod(IBar::class, 'bar', $expectedExtensionMethod2);
        $this->assertSame($expectedExtensionMethod1, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
        $this->assertSame($expectedExtensionMethod2, ExtensionMethodRegistry::getExtensionMethod($foo, 'bar'));
    }

    public function testGettingUnregisteredMethodReturnsNull(): void
    {
        $foo = new class() {
        };
        $this->assertNull(ExtensionMethodRegistry::getExtensionMethod($foo, 'bar'));
    }

    public function testRegisteringMultipleInterfacesMakesExtensionMethodsGettable(): void
    {
        $foo = new class() {
        };
        $bar = new class() {
        };
        $expectedExtensionMethod = fn (): string => 'foo';
        ExtensionMethodRegistry::registerExtensionMethod([$foo::class, $bar::class], 'foo', $expectedExtensionMethod);
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($bar, 'foo'));
    }

    public function testRegisteringMultipleExtensionMethodsToClassMakesThemAllGettable(): void
    {
        $foo = new class() {
        };
        $expectedExtensionMethod1 = fn (): string => 'foo';
        $expectedExtensionMethod2 = fn (): string => 'bar';
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', $expectedExtensionMethod1);
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'bar', $expectedExtensionMethod2);
        $this->assertSame($expectedExtensionMethod1, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
        $this->assertSame($expectedExtensionMethod2, ExtensionMethodRegistry::getExtensionMethod($foo, 'bar'));
    }

    public function testRegisteringSingleInterfaceMakesExtensionMethodCallable(): void
    {
        $foo = new class() {
        };
        $expectedExtensionMethod = fn (): string => 'foo';
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', $expectedExtensionMethod);
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
    }
}
