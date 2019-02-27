<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Input\Compilers;

use Aphiria\Console\Input\Compilers\StringInputCompiler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the string input compiler
 */
class StringInputCompilerTest extends TestCase
{
    /** @var StringInputCompiler */
    private $compiler;

    protected function setUp(): void
    {
        $this->compiler = new StringInputCompiler();
    }

    public function testBackslashesAreRespected(): void
    {
        $input = $this->compiler->compile('foo bar\\baz');
        $this->assertEquals(['bar\\baz'], $input->argumentValues);
    }

    public function testCompilingArgumentShortOptionLongOption(): void
    {
        $input = $this->compiler->compile('foo bar -r --name=dave');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals(['bar'], $input->argumentValues);
        $this->assertNull($input->options['r']);
        $this->assertEquals('dave', $input->options['name']);
    }

    public function testCompilingArrayLongOptionWithEqualsSign(): void
    {
        $input = $this->compiler->compile('foo --name=dave --name=young');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->argumentValues);
        $this->assertEquals(['dave', 'young'], $input->options['name']);
    }

    public function testCompilingArrayLongOptionWithoutEqualsSign(): void
    {
        $input = $this->compiler->compile('foo --name dave --name young');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->argumentValues);
        $this->assertEquals(['dave', 'young'], $input->options['name']);
    }

    public function testCompilingCommandName(): void
    {
        $input = $this->compiler->compile('foo');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->argumentValues);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingLongOptionWithEqualsSign(): void
    {
        $input = $this->compiler->compile('foo --name=dave');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->argumentValues);
        $this->assertEquals('dave', $input->options['name']);
    }

    public function testCompilingLongOptionWithoutEqualsSign(): void
    {
        $input = $this->compiler->compile('foo --name dave');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->argumentValues);
        $this->assertEquals('dave', $input->options['name']);
    }

    public function testCompilingLongOptionWithoutEqualsSignWithArgumentAfter(): void
    {
        $request = $this->compiler->compile('foo --name dave bar');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals(['bar'], $request->argumentValues);
        $this->assertEquals('dave', $request->options['name']);
    }

    public function testCompilingLongOptionWithoutEqualsSignWithQuotedValue(): void
    {
        $input = $this->compiler->compile("foo --first 'dave' --last=\"young\"");
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->argumentValues);
        $this->assertEquals('dave', $input->options['first']);
        $this->assertEquals('young', $input->options['last']);
    }


    public function testCompilingMultipleArgument(): void
    {
        $input = $this->compiler->compile('foo bar baz blah');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals(['bar', 'baz', 'blah'], $input->argumentValues);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingMultipleSeparateShortOptions(): void
    {
        $input = $this->compiler->compile('foo -r -f -d');
        $this->assertEquals('foo', $input->commandName);
        $this->assertNull($input->options['r']);
        $this->assertNull($input->options['f']);
        $this->assertNull($input->options['d']);
        $this->assertEquals([], $input->argumentValues);
    }

    public function testCompilingMultipleShortOptions(): void
    {
        $input = $this->compiler->compile('foo -rfd');
        $this->assertEquals('foo', $input->commandName);
        $this->assertNull($input->options['r']);
        $this->assertNull($input->options['f']);
        $this->assertNull($input->options['d']);
        $this->assertEquals([], $input->argumentValues);
    }

    public function testCompilingSingleArgument(): void
    {
        $input = $this->compiler->compile('foo bar');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals(['bar'], $input->argumentValues);
        $this->assertEquals([], $input->options);
    }

    public function testCompilingSingleShortOption(): void
    {
        $input = $this->compiler->compile('foo -r');
        $this->assertEquals('foo', $input->commandName);
        $this->assertNull($input->options['r']);
        $this->assertEquals([], $input->argumentValues);
    }

    public function testCompilingTwoConsecutiveLongOptions(): void
    {
        $input = $this->compiler->compile('foo --bar --baz');
        $this->assertEquals('foo', $input->commandName);
        $this->assertEquals([], $input->argumentValues);
        $this->assertEquals(null, $input->options['bar']);
        $this->assertEquals(null, $input->options['baz']);
    }
}
