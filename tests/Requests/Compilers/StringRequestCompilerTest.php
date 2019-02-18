<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests\Compilers;

use Aphiria\Console\Requests\Compilers\StringRequestCompiler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the string request compiler
 */
class StringRequestCompilerTest extends TestCase
{
    /** @var StringRequestCompiler */
    private $compiler;

    public function setUp(): void
    {
        $this->compiler = new StringRequestCompiler();
    }

    public function testBackslashesAreRespected(): void
    {
        $request = $this->compiler->compile('foo bar\\baz');
        $this->assertEquals(['bar\\baz'], $request->argumentValues);
    }

    public function testCompilingArgumentShortOptionLongOption(): void
    {
        $request = $this->compiler->compile('foo bar -r --name=dave');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals(['bar'], $request->argumentValues);
        $this->assertNull($request->options['r']);
        $this->assertEquals('dave', $request->options['name']);
    }

    public function testCompilingArrayLongOptionWithEqualsSign(): void
    {
        $request = $this->compiler->compile('foo --name=dave --name=young');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals([], $request->argumentValues);
        $this->assertEquals(['dave', 'young'], $request->options['name']);
    }

    public function testCompilingArrayLongOptionWithoutEqualsSign(): void
    {
        $request = $this->compiler->compile('foo --name dave --name young');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals([], $request->argumentValues);
        $this->assertEquals(['dave', 'young'], $request->options['name']);
    }

    public function testCompilingCommandName(): void
    {
        $request = $this->compiler->compile('foo');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals([], $request->argumentValues);
        $this->assertEquals([], $request->options);
    }

    public function testCompilingLongOptionWithEqualsSign(): void
    {
        $request = $this->compiler->compile('foo --name=dave');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals([], $request->argumentValues);
        $this->assertEquals('dave', $request->options['name']);
    }

    public function testCompilingLongOptionWithoutEqualsSign(): void
    {
        $request = $this->compiler->compile('foo --name dave');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals([], $request->argumentValues);
        $this->assertEquals('dave', $request->options['name']);
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
        $request = $this->compiler->compile("foo --first 'dave' --last=\"young\"");
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals([], $request->argumentValues);
        $this->assertEquals('dave', $request->options['first']);
        $this->assertEquals('young', $request->options['last']);
    }


    public function testCompilingMultipleArgument(): void
    {
        $request = $this->compiler->compile('foo bar baz blah');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals(['bar', 'baz', 'blah'], $request->argumentValues);
        $this->assertEquals([], $request->options);
    }

    public function testCompilingMultipleSeparateShortOptions(): void
    {
        $request = $this->compiler->compile('foo -r -f -d');
        $this->assertEquals('foo', $request->commandName);
        $this->assertNull($request->options['r']);
        $this->assertNull($request->options['f']);
        $this->assertNull($request->options['d']);
        $this->assertEquals([], $request->argumentValues);
    }

    public function testCompilingMultipleShortOptions(): void
    {
        $request = $this->compiler->compile('foo -rfd');
        $this->assertEquals('foo', $request->commandName);
        $this->assertNull($request->options['r']);
        $this->assertNull($request->options['f']);
        $this->assertNull($request->options['d']);
        $this->assertEquals([], $request->argumentValues);
    }

    public function testCompilingSingleArgument(): void
    {
        $request = $this->compiler->compile('foo bar');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals(['bar'], $request->argumentValues);
        $this->assertEquals([], $request->options);
    }

    public function testCompilingSingleShortOption(): void
    {
        $request = $this->compiler->compile('foo -r');
        $this->assertEquals('foo', $request->commandName);
        $this->assertNull($request->options['r']);
        $this->assertEquals([], $request->argumentValues);
    }

    public function testCompilingTwoConsecutiveLongOptions(): void
    {
        $request = $this->compiler->compile('foo --bar --baz');
        $this->assertEquals('foo', $request->commandName);
        $this->assertEquals([], $request->argumentValues);
        $this->assertEquals(null, $request->options['bar']);
        $this->assertEquals(null, $request->options['baz']);
    }
}
