<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
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
    /** @var IOutputCompiler The output compiler to use */
    protected IOutputCompiler $outputCompiler;
    /** @var IDriver The driver */
    protected IDriver $driver;
    /** @var bool Whether or not to include styling on output messages */
    protected bool $includeStyles = true;

    /**
     * @param IOutputCompiler|null $outputCompiler The output compiler to use
     * @param IDriver|null $driver The driver
     */
    public function __construct(IOutputCompiler $outputCompiler = null, IDriver $driver = null)
    {
        $this->outputCompiler = $outputCompiler ?? new OutputCompiler();
        $this->driver = $driver ?? (new DriverSelector())->select();
    }

    /**
     * @inheritdoc
     */
    public function getDriver(): IDriver
    {
        return $this->driver;
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
