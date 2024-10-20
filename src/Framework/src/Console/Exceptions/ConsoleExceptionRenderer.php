<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
    /** @var array<class-string<Exception>, Closure(Exception, IOutput): void|Closure(Exception, IOutput): int|Closure(Exception, IOutput): StatusCode> The mapping of exception types to callbacks that write output and return status codes */
    protected array $outputWriters = [];

    /**
     * @param IOutput $output The output to write to
     * @param bool $shouldExit Whether or not to exit after handling the exception
     */
    public function __construct(
        public IOutput $output = new ConsoleOutput(),
        protected readonly bool $shouldExit = true
    ) {
    }

    /**
     * Registers many writers that can use exceptions to write output and return status codes
     *
     * @template T of Exception
     * @param array<class-string<T>, Closure(T, IOutput): void|Closure(T, IOutput): int> $exceptionTypesToCallbacks The mapping of exception types to callbacks
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
     * @template T of Exception
     * @param class-string<T> $exceptionType The type of exception whose factory we're registering
     * @param Closure(T, IOutput): void|Closure(T, IOutput): int $callback The callback that takes in an exception and output, and writes output/returns a status code
     */
    public function registerOutputWriter(string $exceptionType, Closure $callback): void
    {
        /** @psalm-suppress InvalidPropertyAssignmentValue This is valid - bug */
        $this->outputWriters[$exceptionType] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function render(Exception $ex): void
    {
        if (isset($this->outputWriters[$ex::class])) {
            /** @psalm-suppress PossiblyNullFunctionCall This will never be null - bug */
            $statusCode = $this->outputWriters[$ex::class]($ex, $this->output) ?? StatusCode::Fatal;
        } else {
            $statusCode  = StatusCode::Fatal;
            $this->output->writeln($this->getDefaultExceptionMessages($ex));
        }

        if ($this->shouldExit) {
            // We cannot actually call exit() from a test, even from a separate process
            // @codeCoverageIgnoreStart
            exit($statusCode instanceof StatusCode ? $statusCode->value : $statusCode);
            // @codeCoverageIgnoreEnd
        }
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
