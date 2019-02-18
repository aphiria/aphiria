<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests\Compilers;

use Aphiria\Console\Requests\Compilers\ArgvRequestCompiler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the argv request compiler
 */
class ArgvRequestCompilerTest extends TestCase
{
    /** @var ArgvRequestCompiler */
    private $compiler;

    public function setUp(): void
    {
        $this->compiler = new ArgvRequestCompiler();
    }

    public function testBackslashesAreRespected(): void
    {
        $request = $this->compiler->compile(['apex', 'foo', 'bar\\baz']);
        $this->assertEquals(['bar\\baz'], $request->argumentValues);
    }

    public function testCompilingArgumentsAndOptions(): void
    {
        $request = $this->compiler->compile(['apex', 'foo', 'bar', '-r', '--name=dave']);
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals(['bar'], $request->argumentValues);
        $this->assertNull($request->options['r']);
        $this->assertEquals('dave', $request->options['name']);
    }

    public function testCompilingNullString(): void
    {
        $_SERVER['argv'] = ['apex', 'foo', 'bar', '-r', '--name=dave'];
        $request = $this->compiler->compile(null);
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals(['bar'], $request->argumentValues);
        $this->assertNull($request->options['r']);
        $this->assertEquals('dave', $request->options['name']);
    }

    public function testCompilingOptionWithNoValue(): void
    {
        $request = $this->compiler->compile(['apex', 'foo', '--name']);
        $this->assertNull($request->options['name']);
    }

    public function testPassingInvalidInputType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->compiler->compile('foo');
    }
}
