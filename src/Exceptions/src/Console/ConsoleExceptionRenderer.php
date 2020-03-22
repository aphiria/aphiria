<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Console;

use Aphiria\Console\Output\ConsoleOutput;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCodes;
use Aphiria\Exceptions\IExceptionRenderer;
use Closure;
use Exception;

/**
 * Defines the exception renderer for console applications
 */
class ConsoleExceptionRenderer implements IExceptionRenderer
{
    /** @var IOutput The output to write to */
    protected IOutput $output;
    /** @var bool Whether or not to exit after handling the exception */
    protected bool $shouldExit;
    /** @var Closure[] The mapping of exception types to factories that return exception results */
    protected array $exceptionResultFactories = [];

    /**
     * @param IOutput|null $output The output to write to
     * @param bool $shouldExit Whether or not to exit after handling the exception
     */
    public function __construct(IOutput $output = null, bool $shouldExit = true)
    {
        $this->output = $output ?? new ConsoleOutput();
        $this->shouldExit = $shouldExit;
    }

    /**
     * @inheritdoc
     */
    public function render(Exception $ex): void
    {
        if (isset($this->exceptionResultFactories[\get_class($ex)])) {
            $result = $this->exceptionResultFactories[\get_class($ex)]($ex);
        } else {
            $result = $this->createDefaultExceptionResult($ex);
        }

        $this->output->writeln($result->getMessages());

        if ($this->shouldExit) {
            exit($result->getStatusCode());
        }
    }

    /**
     * Registers a factory that can convert an exception to an exception result
     *
     * @param string $exceptionType The type of exception whose factory we're registering
     * @param Closure $factory The factory that takes in an exception and returns an exception result
     */
    public function registerExceptionResultFactory(string $exceptionType, Closure $factory): void
    {
        $this->exceptionResultFactories[$exceptionType] = $factory;
    }

    /**
     * Registers many factories that can convert exceptions to exception results
     *
     * @param Closure[] $exceptionTypesToFactories The mapping of exception types to factories that return exception results
     */
    public function registerManyExceptionResultFactories(array $exceptionTypesToFactories): void
    {
        foreach ($exceptionTypesToFactories as $exceptionType => $factory) {
            $this->registerExceptionResultFactory($exceptionType, $factory);
        }
    }

    /**
     * Sets the output
     *
     * @param IOutput $output The new output
     */
    public function setOutput(IOutput $output): void
    {
        $this->output = $output;
    }

    /**
     * Creates a default exception result
     *
     * @param Exception $ex The exception that was thrown
     * @return ExceptionResult The default exception result
     */
    protected function createDefaultExceptionResult(Exception $ex): ExceptionResult
    {
        return new ExceptionResult(
            StatusCodes::FATAL,
            "<fatal>{$ex->getMessage()}" . \PHP_EOL . "{$ex->getTraceAsString()}</fatal>"
        );
    }
}
