<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers;

use Aphiria\Console\Output\Compilers\MockOutputCompiler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the mock output compiler
 */
class MockOutputCompilerTest extends TestCase
{
    public function testCompilingStyledMessage(): void
    {
        $compiler = new MockOutputCompiler();
        $this->assertEquals('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>', true));
    }

    public function testCompilingUnstyledMessage(): void
    {
        $compiler = new MockOutputCompiler();
        $this->assertEquals('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>', false));
    }
}
