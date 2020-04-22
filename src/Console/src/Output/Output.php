<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output;

use Aphiria\Console\Drivers\CliDriverSelector;
use Aphiria\Console\Drivers\ICliDriver;
use Aphiria\Console\Output\Compilers\IOutputCompiler;
use Aphiria\Console\Output\Compilers\OutputCompiler;

/**
 * Defines a basic output
 */
abstract class Output implements IOutput
{
    /** @var IOutputCompiler The output compiler to use */
    protected IOutputCompiler $outputCompiler;
    /** @var ICliDriver The CLI driver */
    protected ICliDriver $cliDriver;
    /** @var bool Whether or not to include styling on output messages */
    protected bool $includeStyles = true;

    /**
     * @param IOutputCompiler|null $outputCompiler The output compiler to use
     * @param ICliDriver|null $cliDriver The CLI driver
     */
    public function __construct(IOutputCompiler $outputCompiler = null, ICliDriver $cliDriver = null)
    {
        $this->outputCompiler = $outputCompiler ?? new OutputCompiler();
        $this->cliDriver = $cliDriver ?? (new CliDriverSelector())->select();
    }

    /**
     * @inheritdoc
     */
    public function getCliDriver(): ICliDriver
    {
        return $this->cliDriver;
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
    public function write($messages): void
    {
        foreach ((array)$messages as $message) {
            $this->doWrite($this->outputCompiler->compile($message, $this->includeStyles), false);
        }
    }

    /**
     * @inheritdoc
     */
    public function writeln($messages): void
    {
        foreach ((array)$messages as $message) {
            $this->doWrite($this->outputCompiler->compile($message), true);
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
