<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Extensions\Tests;

use Aphiria\Extensions\Extensions;
use Aphiria\Extensions\Tests\Mocks\IBar;
use Aphiria\Extensions\Tests\Mocks\IFoo;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ExtensionsTest extends TestCase
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
        Extensions::register($foo::class, 'foobar', fn () => $this->getFoo() . 'bar');
        $this->assertSame('foobar', Extensions::call($foo, 'foobar'));
    }

    public function testCallingExtensionMethodOnChildClassOfRegisteredInterfaceStillWorks(): void
    {
        $foo = new class() implements IFoo {
        };
        Extensions::register(IFoo::class, 'foo', fn () => 'bar');
        $this->assertSame('bar', Extensions::call($foo, 'foo'));
    }

    public function testCallingExtensionMethodOnNestedChildClassOfRegisteredInterfaceStillWorks(): void
    {
        $foo = new class() implements IBar {
        };
        Extensions::register(IFoo::class, 'foo', fn () => 'foo');
        Extensions::register(IBar::class, 'bar', fn () => 'bar');
        $this->assertSame('foo', Extensions::call($foo, 'foo'));
        $this->assertSame('bar', Extensions::call($foo, 'bar'));
    }

    public function testCallingUnregisteredMethodThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $foo = new class() {
        };
        $this->expectExceptionMessage($foo::class . '::bar() does not exist');
        Extensions::call($foo, 'bar');
    }

    public function testRegisteringExtensionMethodWithParametersCanBeInvokedWithParameters(): void
    {
        $foo = new class() {
        };
        Extensions::register($foo::class, 'foo', fn (string $param) => $param);
        $this->assertSame('bar', Extensions::call($foo, 'foo', ['bar']));
    }

    public function testRegisteringMultipleInterfaceMakesExtensionMethodCallable(): void
    {
        $foo = new class() {
        };
        $bar = new class() {
        };
        Extensions::register([$foo::class, $bar::class], 'foo', fn () => 'bar');
        $this->assertSame('bar', Extensions::call($foo, 'foo'));
        $this->assertSame('bar', Extensions::call($bar, 'foo'));
    }

    public function testRegisteringMultipleExtensionMethodsMakesThemAllCallable(): void
    {
        $foo = new class() {
        };
        Extensions::register($foo::class, 'foo', fn () => 'foo');
        Extensions::register($foo::class, 'bar', fn () => 'bar');
        $this->assertSame('foo', Extensions::call($foo, 'foo'));
        $this->assertSame('bar', Extensions::call($foo, 'bar'));
    }

    public function testRegisteringSingleInterfaceMakesExtensionMethodCallable(): void
    {
        $foo = new class() {
        };
        Extensions::register($foo::class, 'foo', fn (string $arg) => $arg . 'baz');
        $this->assertSame('barbaz', Extensions::call($foo, 'foo', ['bar']));
    }
}
