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

use Aphiria\Extensions\ExtensionMethods;
use Aphiria\Extensions\Extensions;
use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ExtensionMethodsTest extends TestCase
{
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
        Extensions::register($foo::class, 'foo', fn () => 'bar');
        $this->assertSame('bar', $foo->foo());
    }

    public function testCallingExtensionMethodWithParameters(): void
    {
        $foo = new class() {
            use ExtensionMethods;
        };
        Extensions::register($foo::class, 'foo', fn (string $bar, string $baz) => $bar . $baz);
        $this->assertSame('barbaz', $foo->foo('bar', 'baz'));
    }
}
