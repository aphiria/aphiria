<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers\Elements;

use Aphiria\Console\Output\Compilers\Elements\Colors;
use Aphiria\Console\Output\Compilers\Elements\Style;
use Aphiria\Console\Output\Compilers\Elements\TextStyles;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StyleTest extends TestCase
{
    public function testAddingInvalidTextStyle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $style = new Style();
        $style->addTextStyle('foo');
    }

    public function testAddingInvalidTextStyles(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $style = new Style();
        $style->addTextStyles(['foo']);
    }

    public function testDoubleAddingTextStyle(): void
    {
        $style = new Style();
        $style->addTextStyle(TextStyles::BOLD);
        $style->addTextStyle(TextStyles::BOLD);
        $style->addTextStyles([TextStyles::UNDERLINE, TextStyles::UNDERLINE]);
        $this->assertEquals([TextStyles::BOLD, TextStyles::UNDERLINE], $style->textStyles);
    }

    public function testFormattingEmptyString(): void
    {
        $styles = new Style(Colors::RED, Colors::GREEN, [TextStyles::BOLD, TextStyles::UNDERLINE, TextStyles::BLINK]);
        $this->assertEquals('', $styles->format(''));
    }

    public function testFormattingStringWithAllStyles(): void
    {
        $styles = new Style(Colors::RED, Colors::GREEN, [TextStyles::BOLD, TextStyles::UNDERLINE, TextStyles::BLINK]);
        $this->assertEquals("\033[31;42;1;4;5mfoo\033[39;49;22;24;25m", $styles->format('foo'));
    }

    public function testFormattingStringWithoutStyles(): void
    {
        $styles = new Style();
        $this->assertEquals('foo', $styles->format('foo'));
    }

    public function testNotPassingAnythingInConstructor(): void
    {
        $style = new Style();
        $this->assertNull($style->foregroundColor);
        $this->assertNull($style->backgroundColor);
    }

    public function testPassingColorsInConstructor(): void
    {
        $style = new Style(Colors::BLUE, Colors::GREEN);
        $this->assertEquals(Colors::BLUE, $style->foregroundColor);
        $this->assertEquals(Colors::GREEN, $style->backgroundColor);
    }

    public function testRemovingInvalidTextStyle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $style = new Style();
        $style->addTextStyle(TextStyles::BOLD);
        $style->removeTextStyle('foo');
    }

    public function testRemovingTextStyle(): void
    {
        $style = new Style(null, null, [TextStyles::BOLD]);
        $style->removeTextStyle(TextStyles::BOLD);
        $this->assertEquals([], $style->textStyles);
    }

    public function testSettingBackgroundColor(): void
    {
        $style = new Style();
        $style->backgroundColor = Colors::GREEN;
        $this->assertEquals(Colors::GREEN, $style->backgroundColor);
    }

    public function testSettingForegroundColor(): void
    {
        $style = new Style();
        $style->foregroundColor = Colors::BLUE;
        $this->assertEquals(Colors::BLUE, $style->foregroundColor);
    }

    public function testSettingNullBackgroundColor(): void
    {
        $style = new Style();
        $style->backgroundColor = null;
        $this->assertNull($style->backgroundColor);
    }

    public function testSettingNullForegroundColor(): void
    {
        $style = new Style();
        $style->foregroundColor = null;
        $this->assertNull($style->foregroundColor);
    }
}
