<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests\Compilers;

use Aphiria\Console\Requests\Compilers\ArrayListRequestCompiler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the array list request compiler
 */
class ArrayListRequestCompilerTest extends TestCase
{
    /** @var ArrayListRequestCompiler */
    private $compiler;

    public function setUp(): void
    {
        $this->compiler = new ArrayListRequestCompiler();
    }

    public function testBackslashesAreRespected(): void
    {
        $request = $this->compiler->compile([
            'name' => 'foo',
            'arguments' => ['bar\\baz']
        ]);
        $this->assertEquals(['bar\\baz'], $request->argumentValues);
    }

    public function testNotPassingArguments(): void
    {
        $request = $this->compiler->compile([
            'name' => 'foo',
            'options' => ['--name=dave', '-r']
        ]);
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals([], $request->argumentValues);
        $this->assertNull($request->options['r']);
        $this->assertEquals('dave', $request->options['name']);
    }

    public function testNotPassingOptions(): void
    {
        $request = $this->compiler->compile([
            'name' => 'foo',
            'arguments' => ['bar']
        ]);
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals(['bar'], $request->argumentValues);
        $this->assertEquals([], $request->options);
    }

    public function testCompilingArgumentsAndOptions(): void
    {
        $request = $this->compiler->compile([
            'name' => 'foo',
            'arguments' => ['bar'],
            'options' => ['--name=dave', '-r']
        ]);
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals(['bar'], $request->argumentValues);
        $this->assertNull($request->options['r']);
        $this->assertEquals('dave', $request->options['name']);
    }

    public function testPassingCommandName(): void
    {
        $request = $this->compiler->compile([
            'name' => 'mycommand'
        ]);
        $this->assertEquals('mycommand', $request->commandName);
    }

    public function testPassingInvalidInputType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->compiler->compile('foo');
    }
}
