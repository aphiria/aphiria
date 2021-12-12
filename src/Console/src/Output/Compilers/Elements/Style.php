<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers\Elements;

/**
 * Defines the style of an element
 */
final class Style
{
    /**
     * The list of possible foreground colors
     *
     * @link http://en.wikipedia.org/wiki/ANSI_escape_code
     * @var array<string, array{0: int, 1: int}>
     */
    private array $supportedForegroundColors;
    /**
     * The list of possible background colors
     *
     * @link http://en.wikipedia.org/wiki/ANSI_escape_code
     * @var array<string, array{0: int, 1: int}>
     */
    private array $supportedBackgroundColors;
    /**
     * The list of possible text styles
     *
     * @link http://en.wikipedia.org/wiki/ANSI_escape_code
     * @var array<string, array{0: int, 1: int}>
     */
    private array $supportedTextStyles;

    /**
     * @param Color|null $foregroundColor The foreground color
     * @param Color|null $backgroundColor The background color
     * @param list<TextStyle> $textStyles The list of text styles to apply
     */
    public function __construct(
        public ?Color $foregroundColor = null,
        public ?Color $backgroundColor = null,
        public array $textStyles = []
    ) {
        $this->supportedForegroundColors = [
            Color::Black->value => [30, 39],
            Color::Red->value => [31, 39],
            Color::Green->value => [32, 39],
            Color::Yellow->value => [33, 39],
            Color::Blue->value => [34, 39],
            Color::Magenta->value => [35, 39],
            Color::Cyan->value => [36, 39],
            Color::White->value => [37, 39]
        ];
        $this->supportedBackgroundColors = [
            Color::Black->value => [40, 49],
            Color::Red->value => [41, 49],
            Color::Green->value => [42, 49],
            Color::Yellow->value => [43, 49],
            Color::Blue->value => [44, 49],
            Color::Magenta->value => [45, 49],
            Color::Cyan->value => [46, 49],
            Color::White->value => [47, 49]
        ];
        $this->supportedTextStyles = [
            TextStyle::Bold->value => [1, 22],
            TextStyle::Underline->value => [4, 24],
            TextStyle::Blink->value => [5, 25]
        ];

        $this->addTextStyles($this->textStyles);
    }

    /**
     * Adds the text to have a certain style
     *
     * @param TextStyle $style The name of the text style
     */
    public function addTextStyle(TextStyle $style): void
    {
        // Don't double-add a style
        if (!\in_array($style, $this->textStyles, true)) {
            $this->textStyles[] = $style;
        }
    }

    /**
     * Adds multiple text styles
     *
     * @param list<TextStyle> $styles The names of the text styles
     */
    public function addTextStyles(array $styles): void
    {
        foreach ($styles as $style) {
            $this->addTextStyle($style);
        }
    }

    /**
     * Formats text with the the currently-set styles
     *
     * @param string $text The text to format
     * @return string The formatted text
     */
    public function format(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $startCodes = [];
        $endCodes = [];

        if ($this->foregroundColor !== null) {
            $startCodes[] = $this->supportedForegroundColors[$this->foregroundColor->value][0];
            $endCodes[] = $this->supportedForegroundColors[$this->foregroundColor->value][1];
        }

        if ($this->backgroundColor !== null) {
            $startCodes[] = $this->supportedBackgroundColors[$this->backgroundColor->value][0];
            $endCodes[] = $this->supportedBackgroundColors[$this->backgroundColor->value][1];
        }

        foreach ($this->textStyles as $style) {
            $startCodes[] = $this->supportedTextStyles[$style->value][0];
            $endCodes[] = $this->supportedTextStyles[$style->value][1];
        }

        if (\count($startCodes) === 0 && \count($endCodes) === 0) {
            // No point in trying to format the text
            return $text;
        }

        return \sprintf(
            "\033[%sm%s\033[%sm",
            \implode(';', $startCodes),
            $text,
            \implode(';', $endCodes)
        );
    }

    /**
     * Removes a text style
     *
     * @param TextStyle $style The style to remove
     */
    public function removeTextStyle(TextStyle $style): void
    {
        if (($index = \array_search($style, $this->textStyles, true)) !== false) {
            unset($this->textStyles[$index]);
        }
    }
}
