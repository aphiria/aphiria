<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Exceptions\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Framework\Api\Exceptions\ProblemDetailsExceptionRenderer;
use Aphiria\Framework\Application\AphiriaComponents;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Closure;

/**
 * Defines the exception handler component
 */
class ExceptionHandlerComponent implements IComponent
{
    use AphiriaComponents;

    /** @var IServiceResolver The service resolver */
    private IServiceResolver $serviceResolver;
    /** @var Closure[] The mapping of exception types to problem detail settings */
    private array $exceptionProblemDetailMappings = [];
    /** @var Closure[] The mapping of exception types to console result factories */
    private array $consoleOutputWriters = [];
    /** @var Closure[] The mapping of exception types to log level factories */
    private array $logLevelFactories = [];

    /**
     * @param IServiceResolver $serviceResolver The service resolver
     */
    public function __construct(IServiceResolver $serviceResolver)
    {
        $this->serviceResolver = $serviceResolver;
    }

    /**
     * @inheritdoc
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
     * @param string $exceptionType The type of exception that's thrown
     * @param Closure $callback The factory that takes in the exception and output, and writes messages/returns a status code
     * @return self For chaining
     */
    public function withConsoleOutputWriter(string $exceptionType, Closure $callback): self
    {
        $this->consoleOutputWriters[$exceptionType] = $callback;

        return $this;
    }

    /**
     * Adds a log level factory for a particular exception type
     *
     * @param string $exceptionType The type of exception that's thrown
     * @param Closure $logLevelFactory The factory that takes in an instance of the exception type and returns a PSR-3 log level
     * @return self For chaining
     */
    public function withLogLevelFactory(string $exceptionType, Closure $logLevelFactory): self
    {
        $this->logLevelFactories[$exceptionType] = $logLevelFactory;

        return $this;
    }

    /**
     * Adds a mapping of an exception type to problem details properties
     *
     * @param string $exceptionType The type of exception that's thrown
     * @param string|Closure|null $type The optional problem details type, or a closure that takes in the exception and returns a type, or null
     * @param string|Closure|null $title The optional problem details title, or a closure that takes in the exception and returns a title, or null
     * @param string|Closure|null $detail The optional problem details detail, or a closure that takes in the exception and returns a detail, or null
     * @param int|Closure|null $status The optional problem details status, or a closure that takes in the exception and returns a type, or null
     * @param string|Closure|null $instance The optional problem details instance, or a closure that takes in the exception and returns an instance, or null
     * @param array|Closure|null $extensions The optional problem details extensions, or a closure that takes in the exception and returns an exception, or null
     * @return self For chaining
     */
    public function withProblemDetails(
        string $exceptionType,
        $type = null,
        $title = null,
        $detail = null,
        $status = null,
        $instance = null,
        $extensions = null
    ): self {
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
