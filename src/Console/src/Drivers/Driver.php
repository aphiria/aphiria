<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

use Aphiria\Console\StatusCode;

/**
 * Defines a base CLI driver that's common to multiple OS
 */
abstract class Driver implements IDriver
{
    /** @inheritdoc */
    public int $cliHeight {
        get {
            if ($this->height !== null) {
                return $this->height;
            }

            if (($height = \getenv('LINES')) !== false) {
                return $this->height = (int)$height;
            }

            if (($cliDimensions = $this->getCliDimensionsFromOS()) !== null) {
                // @codeCoverageIgnoreStart
                $this->width = (int)$cliDimensions[0];
                $this->height = (int)$cliDimensions[1];

                return $this->height;
                // @codeCoverageIgnoreEnd
            }

            return $this->height = self::DEFAULT_HEIGHT;
        }
    }
    /** @inheritdoc */
    public int $cliWidth {
        get {
            if ($this->width !== null) {
                return $this->width;
            }

            if (($width = \getenv('COLUMNS')) !== false) {
                return $this->width = (int)$width;
            }

            if (($cliDimensions = $this->getCliDimensionsFromOS()) !== null) {
                $this->width = (int)$cliDimensions[0];
                $this->height = (int)$cliDimensions[1];

                return $this->width;
            }

            return $this->width = self::DEFAULT_WIDTH;
        }
    }
    /** @var int The default height */
    protected const int DEFAULT_HEIGHT = 60;
    /** @var int The default width */
    protected const int DEFAULT_WIDTH = 80;
    /** @var int|null The determined height of the CLI */
    protected ?int $height = null;
    /** @var bool|null Whether or not the CLI support STTY, or null if we haven't checked */
    protected ?bool $supportsStty = null;
    /** @var int|null The determined width of the CLI */
    protected ?int $width = null;

    /**
     * Gets the CLI dimensions as a tuple using OS-specific methods
     *
     * @return array|null The CLI dimensions (width x height), if gettable, otherwise null
     */
    abstract protected function getCliDimensionsFromOS(): ?array;

    /**
     * Gets the CLI dimensions from STTY as a tuple
     *
     * @return array|null The dimensions (width x height) as a tuple if found, otherwise null
     * @codeCoverageIgnore
     */
    protected function getCliDimensionsFromStty(): ?array
    {
        $sttyOutput = $this->runProcess('stty -a | grep columns');

        if ($sttyOutput === null) {
            return null;
        }

        if (
            \preg_match('/rows.(\d+);.columns.(\d+);/i', $sttyOutput, $matches)
            || \preg_match('/;.(\d+).rows;.(\d+).columns/i', $sttyOutput, $matches)
        ) {
            return [(int)$matches[2], (int)$matches[1]];
        }

        return null;
    }

    /**
     * Runs a command in a process and returns its contents
     *
     * @param string $command The command to run in the process
     * @return string|null The output of the process
     * @codeCoverageIgnore
     */
    protected function runProcess(string $command): ?string
    {
        $process = \proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            null,
            null,
            ['suppress_errors' => true]
        );

        if (!\is_resource($process)) {
            return null;
        }

        $sttyOutput = \stream_get_contents($pipes[1]);
        \fclose($pipes[1]);
        \fclose($pipes[2]);
        \proc_close($process);

        return $sttyOutput;
    }

    /**
     * Gets whether or not this driver supports stty
     *
     * @return bool Whether or not STTY is supported
     * @codeCoverageIgnore
     */
    protected function supportsStty(): bool
    {
        if ($this->supportsStty !== null) {
            return $this->supportsStty;
        }

        \exec('stty 2>&1', $output, $sttyCheckStatusCode);

        return $this->supportsStty = (StatusCode::Ok->value === $sttyCheckStatusCode);
    }
}
