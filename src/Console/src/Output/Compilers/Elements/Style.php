<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Compilers\Elements;

use InvalidArgumentException;

/**
 * Defines the style of an element
 */
final class Style
{
    /**
     * The list of possible foreground colors
     *
     * @link http://en.wikipedia.org/wiki/ANSI_escape_code
     * @var array
     */
    private static array $supportedForegroundColors = [
        Colors::BLACK => [30, 39],
        Colors::RED => [31, 39],
        Colors::GREEN => [32, 39],
        Colors::YELLOW => [33, 39],
        Colors::BLUE => [34, 39],
        Colors::MAGENTA => [35, 39],
        Colors::CYAN => [36, 39],
        Colors::WHITE => [37, 39]
    ];
    /**
     * The list of possible background colors
     *
     * @link http://en.wikipedia.org/wiki/ANSI_escape_code
     * @var array
     */
    private static array $supportedBackgroundColors = [
        Colors::BLACK => [40, 49],
        Colors::RED => [41, 49],
        Colors::GREEN => [42, 49],
        Colors::YELLOW => [43, 49],
        Colors::BLUE => [44, 49],
        Colors::MAGENTA => [45, 49],
        Colors::CYAN => [46, 49],
        Colors::WHITE => [47, 49]
    ];
    /**
     * The list of possible text styles
     *
     * @link http://en.wikipedia.org/wiki/ANSI_escape_code
     * @var array
     */
    private static array $supportedTextStyles = [
        TextStyles::BOLD => [1, 22],
        TextStyles::UNDERLINE => [4, 24],
        TextStyles::BLINK => [5, 25]
    ];

    /**
     * @param string|null $foregroundColor The foreground color
     * @param string|null $backgroundColor The background color
     * @param array $textStyles The list of text styles to apply
     */
    public function __construct(
        public ?string $foregroundColor = null,
        public ?string $backgroundColor = null,
        public array $textStyles = [])
    {
        $this->addTextStyles($this->textStyles);
    }

    /**
     * Adds the text to have a certain style
     *
     * @param string $style The name of the text style
     * @throws InvalidArgumentException Thrown if the text style does not exist
     */
    public function addTextStyle(string $style): void
    {
        if (!isset(self::$supportedTextStyles[$style])) {
            throw new InvalidArgumentException("Invalid text style \"$style\"");
        }

        // Don't double-add a style
        if (!\in_array($style, $this->textStyles, true)) {
            $this->textStyles[] = $style;
        }
    }

    /**
     * Adds multiple text styles
     *
     * @param array $styles The names of the text styles
     * @throws InvalidArgumentException Thrown if the text styles do not exist
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
            $startCodes[] = self::$supportedForegroundColors[$this->foregroundColor][0];
            $endCodes[] = self::$supportedForegroundColors[$this->foregroundColor][1];
        }

        if ($this->backgroundColor !== null) {
            $startCodes[] = self::$supportedBackgroundColors[$this->backgroundColor][0];
            $endCodes[] = self::$supportedBackgroundColors[$this->backgroundColor][1];
        }

        foreach ($this->textStyles as $style) {
            $startCodes[] = self::$supportedTextStyles[$style][0];
            $endCodes[] = self::$supportedTextStyles[$style][1];
        }

        if (\count($startCodes) === 0 && \count($endCodes) === 0) {
            // No point in trying to format the text
            return $text;
        }

        return sprintf(
            "\033[%sm%s\033[%sm",
            implode(';', $startCodes),
            $text,
            implode(';', $endCodes)
        );
    }

    /**
     * Removes a text style
     *
     * @param string $style The style to remove
     * @throws InvalidArgumentException Thrown if the text style is invalid
     */
    public function removeTextStyle(string $style): void
    {
        if (!isset(self::$supportedTextStyles[$style])) {
            throw new InvalidArgumentException("Invalid text style \"$style\"");
        }

        if (($index = array_search($style, $this->textStyles, true)) !== false) {
            unset($this->textStyles[$index]);
        }
    }
}
