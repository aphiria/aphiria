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
use Aphiria\ExtensionMethods\ExtensionMethods;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ExtensionMethodsTest extends TestCase
{
    public function testCallingExtensionMethodBindsObjectToClosureScope(): void
    {
        $foo = new class() {
            use ExtensionMethods;

            // This method will only be accessible if the object got bound to the closure scope
            private function getFoo(): string
            {
                return 'foo';
            }
        };
        /** @psalm-suppress UndefinedMethod Intentionally calling a private method here */
        $expectedExtensionMethod = fn (): string => (string)$this->getFoo();
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foobar', $expectedExtensionMethod);
        $this->assertSame('foo', $foo->foobar());
    }

    public function testCallingNonExistentMethodThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $foo = new class() {
            use ExtensionMethods;
        };
        $this->expectExceptionMessage($foo::class . '::bar() does not exist');
        $foo->bar();
    }

    public function testCallingExtensionMethodWithoutParameters(): void
    {
        $foo = new class() {
            use ExtensionMethods;
        };
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', fn () => 'bar');
        $this->assertSame('bar', $foo->foo());
    }

    public function testCallingExtensionMethodWithParameters(): void
    {
        $foo = new class() {
            use ExtensionMethods;
        };
        ExtensionMethodRegistry::registerExtensionMethod($foo::class, 'foo', fn (string $bar, string $baz) => $bar . $baz);
        $this->assertSame('barbaz', $foo->foo('bar', 'baz'));
    }
}
