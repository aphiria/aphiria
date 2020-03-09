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

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Exceptions\Middleware\ExceptionHandler;
use Aphiria\Framework\Middleware\Components\MiddlewareComponent;
use Aphiria\Middleware\MiddlewareBinding;
use Closure;

/**
 * Defines the exception handler component
 */
class ExceptionHandlerComponent implements IComponent
{
    /** @var IServiceResolver The service resolver */
    private IServiceResolver $serviceResolver;
    /** @var IApplicationBuilder The application builder */
    private IApplicationBuilder $appBuilder;
    /** @var bool Whether or not to use the exception handler middleware */
    private bool $exceptionHandlerMiddlewareEnabled = false;
    /** @var Closure[] The mapping of exception types to response factories */
    private array $exceptionResponseFactories = [];
    /** @var Closure[] The mapping of exception types to log level factories */
    private array $logLevelFactories = [];

    /**
     * @param IServiceResolver $serviceResolver The service resolver
     * @param IApplicationBuilder $appBuilder The application builder
     */
    public function __construct(IServiceResolver $serviceResolver, IApplicationBuilder $appBuilder)
    {
        $this->serviceResolver = $serviceResolver;
        $this->appBuilder = $appBuilder;
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        if ($this->exceptionHandlerMiddlewareEnabled) {
            $this->appBuilder->getComponent(MiddlewareComponent::class)
                ->withGlobalMiddleware(new MiddlewareBinding(ExceptionHandler::class), 0);
        }

        $exceptionResponseFactories = $this->serviceResolver->resolve(ExceptionResponseFactoryRegistry::class);
        $logLevelFactories = $this->serviceResolver->resolve(ExceptionLogLevelFactoryRegistry::class);

        $exceptionResponseFactories->registerManyFactories($this->exceptionResponseFactories);
        $logLevelFactories->registerManyFactories($this->logLevelFactories);
    }

    /**
     * Enables the exception handler middleware
     *
     * @return self For chaining
     */
    public function withExceptionHandlerMiddleware(): self
    {
        $this->exceptionHandlerMiddlewareEnabled = true;

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
     * Adds an exception response factory for a particular exception type
     *
     * @param string $exceptionType The type of exception that's thrown
     * @param Closure $responseFactory The factory that takes in an instance of the exception type and returns an HTTP response
     * @return self For chaining
     */
    public function withResponseFactory(string $exceptionType, Closure $responseFactory): self
    {
        $this->exceptionResponseFactories[$exceptionType] = $responseFactory;

        return $this;
    }
}
