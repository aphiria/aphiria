<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses\Compilers;

use Aphiria\Console\Responses\Compilers\Elements\Style;
use Aphiria\Console\Responses\Compilers\Lexers\ResponseLexer;
use Aphiria\Console\Responses\Compilers\Parsers\ResponseParser;
use Aphiria\Console\Responses\Compilers\ResponseCompiler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the response compiler
 */
class ResponseCompilerTest extends TestCase
{
    /** @var ResponseCompiler The compiler to use in tests */
    private $compiler;

    public function setUp(): void
    {
        $this->compiler = new ResponseCompiler(new ResponseLexer(), new ResponseParser());
    }

    public function testCompilingAdjacentElements(): void
    {
        $this->compiler->registerElement('foo', new Style('green', 'white'));
        $this->compiler->registerElement('bar', new Style('cyan'));
        $expectedOutput = "\033[32;47mbaz\033[39;49m\033[36mblah\033[39m";
        $this->assertEquals(
            $expectedOutput,
            $this->compiler->compile('<foo>baz</foo><bar>blah</bar>')
        );
    }

    public function testCompilingElementWithNoChildren(): void
    {
        $this->compiler->registerElement('foo', new Style('green', 'white'));
        $this->compiler->registerElement('bar', new Style('cyan'));
        $expectedOutput = '';
        $this->assertEquals(
            $expectedOutput,
            $this->compiler->compile('<foo></foo>')
        );
    }

    public function testCompilingElementWithoutApplyingStyles(): void
    {
        $this->compiler->setStyled(false);
        $this->compiler->registerElement('foo', new Style('green', 'white'));
        $this->compiler->registerElement('bar', new Style('cyan'));
        $this->assertEquals('bazblah', $this->compiler->compile('<foo>baz</foo><bar>blah</bar>'));
    }

    public function testCompilingEscapedTagAtBeginning(): void
    {
        $this->compiler->registerElement('foo', new Style('green'));
        $expectedOutput = '<bar>';
        $this->assertEquals($expectedOutput, $this->compiler->compile('\\<bar>'));
    }

    public function testCompilingEscapedTagInBetweenTags(): void
    {
        $this->compiler->registerElement('foo', new Style('green'));
        $expectedOutput = "\033[32m<bar>\033[39m";
        $this->assertEquals($expectedOutput, $this->compiler->compile('<foo>\\<bar></foo>'));
    }

    public function testCompilingNestedElements(): void
    {
        $this->compiler->registerElement('foo', new Style('green', 'white'));
        $this->compiler->registerElement('bar', new Style('cyan'));
        $expectedOutput = "\033[32;47m\033[36mbaz\033[39m\033[39;49m";
        $this->assertEquals(
            $expectedOutput,
            $this->compiler->compile('<foo><bar>baz</bar></foo>')
        );
    }

    public function testCompilingNestedElementsWithNoChildren(): void
    {
        $this->compiler->registerElement('foo', new Style('green', 'white'));
        $this->compiler->registerElement('bar', new Style('cyan'));
        $expectedOutput = '';
        $this->assertEquals(
            $expectedOutput,
            $this->compiler->compile('<foo><bar></bar></foo>')
        );
    }

    public function testCompilingNestedElementsWithWordsInBetween(): void
    {
        $this->compiler->registerElement('foo', new Style('green', 'white'));
        $this->compiler->registerElement('bar', new Style('cyan'));
        $expectedOutput = "\033[32;47mbar\033[39;49m\033[32;47m\033[36mblah\033[39m\033[39;49m\033[32;47mbaz\033[39;49m";
        $this->assertEquals(
            $expectedOutput,
            $this->compiler->compile('<foo>bar<bar>blah</bar>baz</foo>')
        );
    }

    public function testCompilingPlainText(): void
    {
        $expectedOutput = 'foobar';
        $this->assertEquals(
            $expectedOutput,
            $this->compiler->compile('foobar')
        );
    }

    public function testCompilingSingleElement(): void
    {
        $this->compiler->registerElement('foo', new Style('green'));
        $expectedOutput = "\033[32mbar\033[39m";
        $this->assertEquals($expectedOutput, $this->compiler->compile('<foo>bar</foo>'));
    }

    public function testCompilingUnclosedElement(): void
    {
        $this->expectException(RuntimeException::class);
        $this->compiler->compile('<foo>bar');
    }

    public function testCompilingUnregisteredElement(): void
    {
        $this->expectException(RuntimeException::class);
        $this->compiler->compile('<foo>bar</foo>');
    }

    public function testIncorrectlyNestedElements(): void
    {
        $this->expectException(RuntimeException::class);
        $this->compiler->registerElement('foo', new Style('green'));
        $this->compiler->registerElement('bar', new Style('blue'));
        $this->compiler->compile('<foo><bar>blah</foo></bar>');
    }
}
