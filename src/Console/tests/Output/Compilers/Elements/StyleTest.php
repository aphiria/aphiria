<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers\Elements;

use Aphiria\Console\Output\Compilers\Elements\Color;
use Aphiria\Console\Output\Compilers\Elements\Style;
use Aphiria\Console\Output\Compilers\Elements\TextStyle;
use PHPUnit\Framework\TestCase;

class StyleTest extends TestCase
{
    public function testDoubleAddingTextStyle(): void
    {
        $style = new Style();
        $style->addTextStyle(TextStyle::Bold);
        $style->addTextStyle(TextStyle::Bold);
        $style->addTextStyles([TextStyle::Underline, TextStyle::Underline]);
        $this->assertEquals([TextStyle::Bold, TextStyle::Underline], $style->textStyles);
    }

    public function testFormattingEmptyString(): void
    {
        $styles = new Style(Color::Red, Color::Green, [TextStyle::Bold, TextStyle::Underline, TextStyle::Blink]);
        $this->assertSame('', $styles->format(''));
    }

    public function testFormattingStringWithAllStyles(): void
    {
        $styles = new Style(Color::Red, Color::Green, [TextStyle::Bold, TextStyle::Underline, TextStyle::Blink]);
        $this->assertSame("\033[31;42;1;4;5mfoo\033[39;49;22;24;25m", $styles->format('foo'));
    }

    public function testFormattingStringWithoutStyles(): void
    {
        $styles = new Style();
        $this->assertSame('foo', $styles->format('foo'));
    }

    public function testNotPassingAnythingInConstructor(): void
    {
        $style = new Style();
        $this->assertNull($style->foregroundColor);
        $this->assertNull($style->backgroundColor);
    }

    public function testPassingColorsInConstructor(): void
    {
        $style = new Style(Color::Blue, Color::Green);
        $this->assertSame(Color::Blue, $style->foregroundColor);
        $this->assertSame(Color::Green, $style->backgroundColor);
    }

    public function testRemovingTextStyle(): void
    {
        $style = new Style(null, null, [TextStyle::Bold]);
        $style->removeTextStyle(TextStyle::Bold);
        $this->assertEquals([], $style->textStyles);
    }
}
