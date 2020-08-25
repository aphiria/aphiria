<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers;

use Aphiria\Console\Output\Compilers\MockOutputCompiler;
use PHPUnit\Framework\TestCase;

class MockOutputCompilerTest extends TestCase
{
    public function testCompilingStyledMessage(): void
    {
        $compiler = new MockOutputCompiler();
        $this->assertSame('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>', true));
    }

    public function testCompilingUnstyledMessage(): void
    {
        $compiler = new MockOutputCompiler();
        $this->assertSame('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>', false));
    }
}
