<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Input\Compilers;

use Aphiria\Console\Input\Compilers\ArrayListInputCompiler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the array list input compiler
 */
class ArrayListInputCompilerTest extends TestCase
{
    /** @var ArrayListInputCompiler */
    private $compiler;

    public function setUp(): void
    {
        $this->compiler = new ArrayListInputCompiler();
    }

    public function testBackslashesAreRespected(): void
    {
        $input = $this->compiler->compile([
            'name' => 'foo',
            'arguments' => ['bar\\baz']
        ]);
        $this->assertEquals(['bar\\baz'], $input->argumentValues);
    }

    public function testNotPassingArguments(): void
    {
        $input = $this->compiler->compile([
            'name' => 'foo',
            'options' => ['--name=dave', '-r']
        ]);
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->argumentValues);
        $this->assertNull($input->options['r']);
        $this->assertEquals('dave', $input->options['name']);
    }

    public function testNotPassingOptions(): void
    {
        $input = $this->compiler->compile([
            'name' => 'foo',
            'arguments' => ['bar']
        ]);
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals(['bar'], $input->argumentValues);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingArgumentsAndOptions(): void
    {
        $input = $this->compiler->compile([
            'name' => 'foo',
            'arguments' => ['bar'],
            'options' => ['--name=dave', '-r']
        ]);
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals(['bar'], $input->argumentValues);
        $this->assertNull($input->options['r']);
        $this->assertEquals('dave', $input->options['name']);
    }

    public function testPassingCommandName(): void
    {
        $input = $this->compiler->compile([
            'name' => 'mycommand'
        ]);
        $this->assertEquals('mycommand', $input->commandName);
    }

    public function testPassingInvalidInputType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->compiler->compile('foo');
    }
}
