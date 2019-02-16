<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses\Compilers;

use Aphiria\Console\Responses\Compilers\MockCompiler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the mock compiler
 */
class MockCompilerTest extends TestCase
{
    /**
     * Tests compiling a styled message
     */
    public function testCompilingStyledMessage(): void
    {
        $compiler = new MockCompiler();
        $compiler->setStyled(true);
        $this->assertEquals('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>'));
    }

    /**
     * Tests compiling an unstyled message
     */
    public function testCompilingUnstyledMessage(): void
    {
        $compiler = new MockCompiler();
        $compiler->setStyled(false);
        $this->assertEquals('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>'));
    }
}
