<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Exceptions\Components;

use Aphiria\Application\IComponent;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Framework\Api\Exceptions\ProblemDetailsExceptionRenderer;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Aphiria\Net\Http\HttpStatusCode;
use Closure;
use Exception;

/**
 * Defines the exception handler component
 */
class ExceptionHandlerComponent implements IComponent
{
    /** @var array<class-string<Exception>, Closure(Exception, IOutput): void|Closure(mixed, IOutput): int|Closure(mixed, IOutput): StatusCode> The mapping of exception types to console result factories */
    private array $consoleOutputWriters = [];
    /** @var array<class-string<Exception>, array{type: string|Closure(Exception): string|null, title: string|Closure(Exception): string|null, detail: string|Closure(Exception): string|null, status: HttpStatusCode|int|Closure(Exception): HttpStatusCode|Closure(Exception): int, instance: string|Closure(Exception): string|null, extensions: array|Closure(Exception): array|null}> The mapping of exception types to problem detail settings */
    private array $exceptionProblemDetailMappings = [];
    /** @var array<class-string<Exception>, Closure(Exception): string> The mapping of exception types to log level factories */
    private array $logLevelFactories = [];

    /**
     * @param IServiceResolver $serviceResolver The service resolver
     */
    public function __construct(private readonly IServiceResolver $serviceResolver)
    {
    }

    /**
     * @inheritdoc
     * @throws ResolutionException Thrown if any dependencies could not be resolved
     */
    public function build(): void
    {
        /** @var IApiExceptionRenderer|null $apiExceptionRenderer */
        $apiExceptionRenderer = null;

        if ($this->serviceResolver->tryResolve(IApiExceptionRenderer::class, $apiExceptionRenderer)) {
            $this->configureApiExceptionRenderer($apiExceptionRenderer);
        }

        /** @var ConsoleExceptionRenderer|null $consoleExceptionRenderer */
        $consoleExceptionRenderer = null;

        if ($this->serviceResolver->tryResolve(ConsoleExceptionRenderer::class, $consoleExceptionRenderer)) {
            $consoleExceptionRenderer->registerManyOutputWriters($this->consoleOutputWriters);
        }

        $logLevelFactory = $this->serviceResolver->resolve(LogLevelFactory::class);
        $logLevelFactory->registerManyLogLevelFactories($this->logLevelFactories);
    }

    /**
     * Adds a console exception output writer
     *
     * @template T of Exception
     * @param class-string<T> $exceptionType The type of exception that's thrown
     * @param Closure(T, IOutput): void|Closure(T, IOutput): int|Closure(T, IOutput): StatusCode $callback The factory that takes in the exception and output, and writes messages/returns a status code
     * @return static For chaining
     */
    public function withConsoleOutputWriter(string $exceptionType, Closure $callback): static
    {
        /** @psalm-suppress InvalidPropertyAssignmentValue This is valid - bug */
        $this->consoleOutputWriters[$exceptionType] = $callback;

        return $this;
    }

    /**
     * Adds a log level factory for a particular exception type
     *
     * @template T of Exception
     * @param class-string<T> $exceptionType The type of exception that's thrown
     * @param Closure(T): string $logLevelFactory The factory that takes in an instance of the exception type and returns a PSR-3 log level
     * @return static For chaining
     */
    public function withLogLevelFactory(string $exceptionType, Closure $logLevelFactory): static
    {
        /** @psalm-suppress InvalidPropertyAssignmentValue This is valid - bug */
        $this->logLevelFactories[$exceptionType] = $logLevelFactory;

        return $this;
    }

    /**
     * Adds a mapping of an exception type to problem details properties
     *
     * @template T of Exception
     * @param class-string<T> $exceptionType The type of exception that's thrown
     * @param string|null|Closure(T): string $type The optional problem details type, or a closure that takes in the exception and returns a type, or null
     * @param string|null|Closure(T): string $title The optional problem details title, or a closure that takes in the exception and returns a title, or null
     * @param string|null|Closure(T): string $detail The optional problem details detail, or a closure that takes in the exception and returns a detail, or null
     * @param HttpStatusCode|int|Closure(T): HttpStatusCode|Closure(T): int $status The optional problem details status, or a closure that takes in the exception and returns a type, or null
     * @param string|null|Closure(T): string $instance The optional problem details instance, or a closure that takes in the exception and returns an instance, or null
     * @param array|null|Closure(T): array $extensions The optional problem details extensions, or a closure that takes in the exception and returns an exception, or null
     * @return static For chaining
     */
    public function withProblemDetails(
        string $exceptionType,
        string|Closure|null $type = null,
        string|Closure|null $title = null,
        string|Closure|null $detail = null,
        HttpStatusCode|int|Closure $status = HttpStatusCode::InternalServerError,
        string|Closure|null $instance = null,
        array|Closure|null $extensions = null
    ): static {
        /** @psalm-suppress PropertyTypeCoercion Psalm doesn't like mixing generics of different types */
        $this->exceptionProblemDetailMappings[$exceptionType] = [
            'type' => $type,
            'title' => $title,
            'detail' => $detail,
            'status' => $status,
            'instance' => $instance,
            'extensions' => $extensions
        ];

        return $this;
    }

    /**
     * Configures the API exception renderer
     *
     * @param IApiExceptionRenderer $apiExceptionRenderer The API exception renderer to configure
     */
    protected function configureApiExceptionRenderer(IApiExceptionRenderer $apiExceptionRenderer): void
    {
        if ($apiExceptionRenderer instanceof ProblemDetailsExceptionRenderer) {
            foreach ($this->exceptionProblemDetailMappings as $exceptionType => $problemDetailProperties) {
                $apiExceptionRenderer->mapExceptionToProblemDetails(
                    $exceptionType,
                    $problemDetailProperties['type'],
                    $problemDetailProperties['title'],
                    $problemDetailProperties['detail'],
                    $problemDetailProperties['status'],
                    $problemDetailProperties['instance'],
                    $problemDetailProperties['extensions'],
                );
            }
        }
    }
}
