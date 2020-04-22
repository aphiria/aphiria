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

use Aphiria\Console\StatusCodes;

/**
 * Defines a base CLI driver that's common to multiple OS
 */
abstract class CliDriver implements ICliDriver
{
    /** @var int The default height */
    protected const DEFAULT_HEIGHT = 60;
    /** @var int The default width */
    protected const DEFAULT_WIDTH = 80;
    /** @var int|null The determine height of the CLI */
    protected ?int $height = null;
    /** @var int|null The determine width of the CLI */
    protected ?int $width = null;
    /** @var bool|null Whether or not the CLI support STTY, or null if we haven't checked */
    protected ?bool $supportsStty = null;

    /**
     * @inheritdoc
     */
    public function getCliHeight(): int
    {
        if ($this->height !== null) {
            return $this->height;
        }

        if (($height = \getenv('LINES')) !== false) {
            return $this->height = (int)$height;
        }

        if (($cliDimensions = $this->getCliDimensionsFromOS()) !== null) {
            // @codeCoverageIgnoreStart
            $this->width = $cliDimensions[0];
            $this->height = $cliDimensions[1];

            return $this->height;
            // @codeCoverageIgnoreEnd
        }

        return $this->height = self::DEFAULT_HEIGHT;
    }

    /**
     * @inheritdoc
     */
    public function getCliWidth(): int
    {
        if ($this->width !== null) {
            return $this->width;
        }

        if (($width = \getenv('COLUMNS')) !== false) {
            return $this->width = (int)$width;
        }

        if (($cliDimensions = $this->getCliDimensionsFromOS()) !== null) {
            $this->width = $cliDimensions[0];
            $this->height = $cliDimensions[1];

            return $this->width;
        }

        return $this->width = self::DEFAULT_WIDTH;
    }

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
            preg_match('/rows.(\d+);.columns.(\d+);/i', $sttyOutput, $matches)
            || preg_match('/;.(\d+).rows;.(\d+).columns/i', $sttyOutput, $matches)
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
        $process = proc_open(
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

        $sttyOutput = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

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

        exec('stty 2>&1', $output, $sttyCheckStatusCode);

        return $this->supportsStty = (StatusCodes::OK === $sttyCheckStatusCode);
    }
}
