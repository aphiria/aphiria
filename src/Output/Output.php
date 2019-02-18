<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Output;

use Aphiria\Console\Output\Compilers\IOutputCompiler;
use Aphiria\Console\Output\Compilers\OutputCompiler;

/**
 * Defines a basic output
 */
abstract class Output implements IOutput
{
    /** @var IOutputCompiler The output compiler to use */
    protected $outputCompiler;

    /**
     * @param IOutputCompiler|null $outputCompiler The output compiler to use
     */
    public function __construct(IOutputCompiler $outputCompiler = null)
    {
        $this->outputCompiler = $outputCompiler ?? new OutputCompiler();
    }

    /**
     * @inheritdoc
     */
    public function setStyled(bool $isStyled): void
    {
        $this->outputCompiler->setStyled($isStyled);
    }

    /**
     * @inheritdoc
     */
    public function write($messages): void
    {
        foreach ((array)$messages as $message) {
            $this->doWrite($this->outputCompiler->compile($message), false);
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
