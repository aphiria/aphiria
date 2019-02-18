<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Input\Compilers;

use Aphiria\Console\Input\Compilers\ArgvInputCompiler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the argv input compiler
 */
class ArgvInputCompilerTest extends TestCase
{
    /** @var ArgvInputCompiler */
    private $compiler;

    public function setUp(): void
    {
        $this->compiler = new ArgvInputCompiler();
    }

    public function testBackslashesAreRespected(): void
    {
        $input = $this->compiler->compile(['aphiria', 'foo', 'bar\\baz']);
        $this->assertEquals(['bar\\baz'], $input->argumentValues);
    }

    public function testCompilingArgumentsAndOptions(): void
    {
        $input = $this->compiler->compile(['aphiria', 'foo', 'bar', '-r', '--name=dave']);
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals(['bar'], $input->argumentValues);
        $this->assertNull($input->options['r']);
        $this->assertEquals('dave', $input->options['name']);
    }

    public function testCompilingNullString(): void
    {
        $_SERVER['argv'] = ['aphiria', 'foo', 'bar', '-r', '--name=dave'];
        $input = $this->compiler->compile(null);
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals(['bar'], $input->argumentValues);
        $this->assertNull($input->options['r']);
        $this->assertEquals('dave', $input->options['name']);
    }

    public function testCompilingOptionWithNoValue(): void
    {
        $input = $this->compiler->compile(['aphiria', 'foo', '--name']);
        $this->assertNull($input->options['name']);
    }

    public function testPassingInvalidInputType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->compiler->compile('foo');
    }
}
