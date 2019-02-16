<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses;

use Aphiria\Console\Responses\Compilers\ICompiler;

/**
 * Defines a basic response
 */
abstract class Response implements IResponse
{
    /** @var ICompiler The response compiler to use */
    protected $compiler;

    /**
     * @param ICompiler $compiler The response compiler to use
     */
    public function __construct(ICompiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * @inheritdoc
     */
    public function setStyled(bool $isStyled): void
    {
        $this->compiler->setStyled($isStyled);
    }

    /**
     * @inheritdoc
     */
    public function write($messages): void
    {
        foreach ((array)$messages as $message) {
            $this->doWrite($this->compiler->compile($message), false);
        }
    }

    /**
     * @inheritdoc
     */
    public function writeln($messages): void
    {
        foreach ((array)$messages as $message) {
            $this->doWrite($this->compiler->compile($message), true);
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
