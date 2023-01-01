<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers;

use Aphiria\Console\Output\Compilers\Elements\Color;
use Aphiria\Console\Output\Compilers\Elements\Element;
use Aphiria\Console\Output\Compilers\Elements\ElementRegistry;
use Aphiria\Console\Output\Compilers\Elements\Style;
use Aphiria\Console\Output\Compilers\OutputCompiler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class OutputCompilerTest extends TestCase
{
    private ElementRegistry $elements;
    private OutputCompiler $compiler;

    protected function setUp(): void
    {
        $this->elements = new ElementRegistry();
        $this->compiler = new OutputCompiler($this->elements);
    }

    public function testCompilingAdjacentElements(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green, Color::White)));
        $this->elements->registerElement(new Element('bar', new Style(Color::Cyan)));
        $expectedOutput = "\033[32;47mbaz\033[39;49m\033[36mblah\033[39m";
        $this->assertSame(
            $expectedOutput,
            $this->compiler->compile('<foo>baz</foo><bar>blah</bar>')
        );
    }

    public function testCompilingElementWithNoChildren(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green, Color::White)));
        $this->elements->registerElement(new Element('bar', new Style(Color::Cyan)));
        $expectedOutput = '';
        $this->assertSame(
            $expectedOutput,
            $this->compiler->compile('<foo></foo>')
        );
    }

    public function testCompilingElementWithoutApplyingStyles(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green, Color::White)));
        $this->elements->registerElement(new Element('bar', new Style(Color::Cyan)));
        $this->assertSame('bazblah', $this->compiler->compile('<foo>baz</foo><bar>blah</bar>', false));
    }

    public function testCompilingElementWithZeroAsInnerText(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green)));
        $this->assertSame("\033[32m0\033[39m", $this->compiler->compile('<foo>0</foo>'));
    }

    public function testCompilingEscapedTagAtBeginning(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green)));
        $expectedOutput = '<bar>';
        $this->assertSame($expectedOutput, $this->compiler->compile('\\<bar>'));
    }

    public function testCompilingEscapedTagInBetweenTags(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green)));
        $expectedOutput = "\033[32m<bar>\033[39m";
        $this->assertSame($expectedOutput, $this->compiler->compile('<foo>\\<bar></foo>'));
    }

    public function testCompilingNestedElements(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green, Color::White)));
        $this->elements->registerElement(new Element('bar', new Style(Color::Cyan)));
        $expectedOutput = "\033[32;47m\033[36mbaz\033[39m\033[39;49m";
        $this->assertSame(
            $expectedOutput,
            $this->compiler->compile('<foo><bar>baz</bar></foo>')
        );
    }

    public function testCompilingNestedElementsWithNoChildren(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green, Color::White)));
        $this->elements->registerElement(new Element('bar', new Style(Color::Cyan)));
        $expectedOutput = '';
        $this->assertSame(
            $expectedOutput,
            $this->compiler->compile('<foo><bar></bar></foo>')
        );
    }

    public function testCompilingNestedElementsWithWordsInBetween(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green, Color::White)));
        $this->elements->registerElement(new Element('bar', new Style(Color::Cyan)));
        $expectedOutput = "\033[32;47mbar\033[39;49m\033[32;47m\033[36mblah\033[39m\033[39;49m\033[32;47mbaz\033[39;49m";
        $this->assertSame(
            $expectedOutput,
            $this->compiler->compile('<foo>bar<bar>blah</bar>baz</foo>')
        );
    }

    public function testCompilingPlainText(): void
    {
        $expectedOutput = 'foobar';
        $this->assertSame(
            $expectedOutput,
            $this->compiler->compile('foobar')
        );
    }

    public function testCompilingSingleElement(): void
    {
        $this->elements->registerElement(new Element('foo', new Style(Color::Green)));
        $expectedOutput = "\033[32mbar\033[39m";
        $this->assertSame($expectedOutput, $this->compiler->compile('<foo>bar</foo>'));
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
        $this->elements->registerElement(new Element('foo', new Style(Color::Green)));
        $this->elements->registerElement(new Element('bar', new Style(Color::Blue)));
        $this->compiler->compile('<foo><bar>blah</foo></bar>');
    }
}
