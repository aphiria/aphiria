<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

/**
 * Defines a base terminal driver that's common to multiple OS
 */
abstract class TerminalDriver implements ITerminalDriver
{
    /** @var int The default height */
    protected const DEFAULT_HEIGHT = 60;
    /** @var int The default width */
    protected const DEFAULT_WIDTH = 80;
    /** @var int|null The determine height of the terminal */
    protected ?int $height;
    /** @var int|null The determine width of the terminal */
    protected ?int $width;

    /**
     * @inheritdoc
     */
    public function getTerminalHeight(): int
    {
        if ($this->height !== null) {
            return $this->height;
        }

        if (($height = \getenv('LINES')) !== false) {
            return $this->height = (int)$height;
        }

        if (($height = $this->getTerminalHeightFromOs()) !== null) {
            return $this->height = $height;
        }

        return $this->height = self::DEFAULT_HEIGHT;
    }

    /**
     * @inheritdoc
     */
    public function getTerminalWidth(): int
    {
        if ($this->width !== null) {
            return $this->width;
        }

        if (($width = \getenv('COLUMNS')) !== false) {
            return $this->width = (int)$width;
        }

        if (($width = $this->getTerminalWidthFromOs()) !== null) {
            return $this->width = $width;
        }

        return $this->width = self::DEFAULT_WIDTH;
    }

    /**
     * Gets the terminal height using OS-specific methods
     *
     * @return int|null The terminal height, if gettable, otherwise null
     */
    abstract protected function getTerminalHeightFromOs(): ?int;

    /**
     * Gets the terminal width using OS-specific methods
     *
     * @return int|null The terminal width, if gettable, otherwise null
     */
    abstract protected function getTerminalWidthFromOs(): ?int;
}
