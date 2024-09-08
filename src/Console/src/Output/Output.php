<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output;

use Aphiria\Console\Drivers\DriverSelector;
use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Output\Compilers\IOutputCompiler;
use Aphiria\Console\Output\Compilers\OutputCompiler;

/**
 * Defines a basic output
 */
abstract class Output implements IOutput
{
    /** @inheritdoc */
    public readonly IDriver $driver;
    /** @var bool Whether or not to include styling on output messages */
    protected bool $includeStyles = true;

    /**
     * @param IOutputCompiler $outputCompiler The output compiler to use
     * @param IDriver|null $driver The driver
     */
    public function __construct(
        protected readonly IOutputCompiler $outputCompiler = new OutputCompiler(),
        ?IDriver $driver = null
    ) {
        $this->driver = $driver ?? (new DriverSelector())->select();
    }

    /**
     * @inheritdoc
     */
    public function includeStyles(bool $includeStyles): void
    {
        $this->includeStyles = $includeStyles;
    }

    /**
     * @inheritdoc
     */
    public function write(string|array $messages): void
    {
        foreach ((array)$messages as $message) {
            $this->doWrite($this->outputCompiler->compile((string)$message, $this->includeStyles), false);
        }
    }

    /**
     * @inheritdoc
     */
    public function writeln(string|array $messages): void
    {
        foreach ((array)$messages as $message) {
            $this->doWrite($this->outputCompiler->compile((string)$message), true);
        }
    }

    /**
     * Actually performs the writing
     *
     * @param string $message The message to write
     * @param bool $includeNewLine True if we are to include a new line character at the end of the message
     */
    abstract protected function doWrite(string $message, bool $includeNewLine): void;
}
