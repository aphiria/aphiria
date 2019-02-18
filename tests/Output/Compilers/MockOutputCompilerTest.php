<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

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
        $compiler->setStyled(true);
        $this->assertEquals('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>'));
    }

    public function testCompilingUnstyledMessage(): void
    {
        $compiler = new MockOutputCompiler();
        $compiler->setStyled(false);
        $this->assertEquals('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>'));
    }
}
