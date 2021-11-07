<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Exceptions;

use Aphiria\Console\Output\ConsoleOutput;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\Exceptions\IExceptionRenderer;
use Closure;
use Exception;

/**
 * Defines the exception renderer for console applications
 */
class ConsoleExceptionRenderer implements IExceptionRenderer
{
    /** @var array<class-string<Exception>, Closure(mixed, IOutput): void|Closure(mixed, IOutput): int> The mapping of exception types to callbacks that write output and return status codes */
    protected array $outputWriters = [];

    /**
     * @param IOutput $output The output to write to
     * @param bool $shouldExit Whether or not to exit after handling the exception
     */
    public function __construct(
        protected IOutput $output = new ConsoleOutput(),
        protected readonly bool $shouldExit = true
    ) {
    }

    /**
     * @inheritdoc
     */
    public function render(Exception $ex): void
    {
        if (isset($this->outputWriters[$ex::class])) {
            $statusCode = $this->outputWriters[$ex::class]($ex, $this->output) ?? StatusCode::Fatal;
        } else {
            $statusCode  = StatusCode::Fatal;
            $this->output->writeln($this->getDefaultExceptionMessages($ex));
        }

        if ($this->shouldExit) {
            // We cannot actually call exit() from a test, even from a separate process
            // @codeCoverageIgnoreStart
            exit($statusCode);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Registers many writers that can use exceptions to write output and return status codes
     *
     * @param array<class-string<Exception>, Closure(mixed, IOutput): void|Closure(mixed, IOutput): int> $exceptionTypesToCallbacks The mapping of exception types to callbacks
     */
    public function registerManyOutputWriters(array $exceptionTypesToCallbacks): void
    {
        foreach ($exceptionTypesToCallbacks as $exceptionType => $callback) {
            $this->registerOutputWriter($exceptionType, $callback);
        }
    }

    /**
     * Registers a callback that can use an exception to write output and return a status code
     *
     * @param class-string<Exception> $exceptionType The type of exception whose factory we're registering
     * @param Closure(mixed, IOutput): void|Closure(mixed, IOutput): int $callback The callback that takes in an exception and output, and writes output/returns a status code
     */
    public function registerOutputWriter(string $exceptionType, Closure $callback): void
    {
        $this->outputWriters[$exceptionType] = $callback;
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
     * Creates a default exception message
     *
     * @param Exception $ex The exception that was thrown
     * @return list<string> The default exception messages
     */
    protected function getDefaultExceptionMessages(Exception $ex): array
    {
        return ["<fatal>{$ex->getMessage()}" . \PHP_EOL . "{$ex->getTraceAsString()}</fatal>"];
    }
}
