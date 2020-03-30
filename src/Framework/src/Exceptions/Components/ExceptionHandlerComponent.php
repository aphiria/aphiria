<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Exceptions\Components;

use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Framework\Api\Exceptions\ApiExceptionRenderer;
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
    /** @var Closure[] The mapping of exception types to HTTP response factories */
    private array $httpResponseFactories = [];
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
        /** @var ApiExceptionRenderer|null $apiExceptionRenderer */
        $apiExceptionRenderer = null;

        if ($this->serviceResolver->tryResolve(ApiExceptionRenderer::class, $apiExceptionRenderer)) {
            $apiExceptionRenderer->registerManyResponseFactories($this->httpResponseFactories);
        }

        /** @var ConsoleExceptionRenderer|null $consoleExceptionRenderer */
        $consoleExceptionRenderer = null;

        if ($this->serviceResolver->tryResolve(ConsoleExceptionRenderer::class, $consoleExceptionRenderer)) {
            $consoleExceptionRenderer->registerManyOutputWriters($this->consoleOutputWriters);
        }

        $globalExceptionHandler = $this->serviceResolver->resolve(GlobalExceptionHandler::class);
        $globalExceptionHandler->registerManyLogLevelFactories($this->logLevelFactories);
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
     * Adds an HTTP exception response factory for a particular exception type
     *
     * @param string $exceptionType The type of exception that's thrown
     * @param Closure $responseFactory The factory that takes in an instance of the exception, the request, and the response factory, and returns a response
     * @return self For chaining
     */
    public function withHttpResponseFactory(string $exceptionType, Closure $responseFactory): self
    {
        $this->httpResponseFactories[$exceptionType] = $responseFactory;

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
}
