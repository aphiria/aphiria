<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\ErrorMessages;

use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageCompiler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the string replacement error message compiler
 */
class StringReplaceErrorMessageCompilerTest extends TestCase
{
    private StringReplaceErrorMessageCompiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new StringReplaceErrorMessageCompiler();
    }

    public function testErrorMessageIdWithNoPlaceholdersIsReturnedIntact(): void
    {
        $this->assertEquals('foo bar', $this->compiler->compile('foo bar'));
    }

    public function testLeftoverUnusedPlaceholdersAreRemovedFromCompiledErrorMessage(): void
    {
        $this->assertEquals('foo ', $this->compiler->compile('foo {bar}'));
    }

    public function testPlaceholdersArePopulated(): void
    {
        $this->assertEquals(
            'foo dave young',
            $this->compiler->compile('foo {bar} {baz}', ['bar' => 'dave', 'baz' => 'young'])
        );
    }
}
