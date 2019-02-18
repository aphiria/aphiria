<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses\Compilers;

use Aphiria\Console\Responses\Compilers\MockResponseCompiler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the mock response compiler
 */
class MockResponseCompilerTest extends TestCase
{
    public function testCompilingStyledMessage(): void
    {
        $compiler = new MockResponseCompiler();
        $compiler->setStyled(true);
        $this->assertEquals('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>'));
    }

    public function testCompilingUnstyledMessage(): void
    {
        $compiler = new MockResponseCompiler();
        $compiler->setStyled(false);
        $this->assertEquals('<foo>bar</foo>', $compiler->compile('<foo>bar</foo>'));
    }
}
