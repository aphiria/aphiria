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
use Aphiria\ExtensionMethods\Tests\Mocks\IBar;
use Aphiria\ExtensionMethods\Tests\Mocks\IFoo;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ExtensionMethodRegistryTest extends TestCase
{
    public function testCallingExtensionMethodBindsObjectToClosureScope(): void
    {
        $foo = new class() {
            // This method will only be accessible if the object got bound to the closure scope
            private function getFoo(): string
            {
                return 'foo';
            }
        };
        /** @psalm-suppress UndefinedMethod Intentionally calling a private method here */
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foobar', fn () => $this->getFoo() . 'bar');
        $this->assertSame('foobar', ExtensionMethodRegistry::call($foo, 'foobar'));
    }

    public function testCallingExtensionMethodMultipleTimesUsesMemoizedClosure(): void
    {
        // Note: This is mainly done for code coverage purposes
        $foo = new class() {
        };
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', fn () => 'foo');
        $this->assertSame('foo', ExtensionMethodRegistry::call($foo, 'foo'));
        $this->assertSame('foo', ExtensionMethodRegistry::call($foo, 'foo'));
    }

    public function testCallingExtensionMethodOnChildClassOfRegisteredInterfaceStillWorks(): void
    {
        $foo = new class() implements IFoo {
        };
        ExtensionMethodRegistry::registerExtensionMethod(IFoo::class, 'foo', fn () => 'bar');
        $this->assertSame('bar', ExtensionMethodRegistry::call($foo, 'foo'));
    }

    public function testCallingExtensionMethodOnNestedChildClassOfRegisteredInterfaceStillWorks(): void
    {
        $foo = new class() implements IBar {
        };
        ExtensionMethodRegistry::registerExtensionMethod(IFoo::class, 'foo', fn () => 'foo');
        ExtensionMethodRegistry::registerExtensionMethod(IBar::class, 'bar', fn () => 'bar');
        $this->assertSame('foo', ExtensionMethodRegistry::call($foo, 'foo'));
        $this->assertSame('bar', ExtensionMethodRegistry::call($foo, 'bar'));
    }

    public function testCallingUnregisteredMethodThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $foo = new class() {
        };
        $this->expectExceptionMessage($foo::class . '::bar() does not exist');
        ExtensionMethodRegistry::call($foo, 'bar');
    }

    public function testRegisteringExtensionMethodWithParametersCanBeInvokedWithParameters(): void
    {
        $foo = new class() {
        };
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', fn (string $param) => $param);
        $this->assertSame('bar', ExtensionMethodRegistry::call($foo, 'foo', ['bar']));
    }

    public function testRegisteringMultipleInterfaceMakesExtensionMethodCallable(): void
    {
        $foo = new class() {
        };
        $bar = new class() {
        };
        ExtensionMethodRegistry::registerExtensionMethod([$foo::class, $bar::class], 'foo', fn () => 'bar');
        $this->assertSame('bar', ExtensionMethodRegistry::call($foo, 'foo'));
        $this->assertSame('bar', ExtensionMethodRegistry::call($bar, 'foo'));
    }

    public function testRegisteringMultipleExtensionMethodsMakesThemAllCallable(): void
    {
        $foo = new class() {
        };
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', fn () => 'foo');
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'bar', fn () => 'bar');
        $this->assertSame('foo', ExtensionMethodRegistry::call($foo, 'foo'));
        $this->assertSame('bar', ExtensionMethodRegistry::call($foo, 'bar'));
    }

    public function testRegisteringSingleInterfaceMakesExtensionMethodCallable(): void
    {
        $foo = new class() {
        };
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', fn (string $arg) => $arg . 'baz');
        $this->assertSame('barbaz', ExtensionMethodRegistry::call($foo, 'foo', ['bar']));
    }
}
