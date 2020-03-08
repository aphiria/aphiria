<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Exceptions\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\Builders\IComponentBuilder;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Exceptions\Middleware\ExceptionHandler;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilder;
use Aphiria\Middleware\MiddlewareBinding;
use Closure;

/**
 * Defines the exception handler component builder
 */
class ExceptionHandlerBuilder implements IComponentBuilder
{
    /** @var ExceptionResponseFactoryRegistry The exception response factories */
    private ExceptionResponseFactoryRegistry $exceptionResponseFactories;
    /** @var ExceptionLogLevelFactoryRegistry The exception log level factories */
    private ExceptionLogLevelFactoryRegistry $logLevelFactories;

    /**
     * @param ExceptionResponseFactoryRegistry $exceptionResponseFactories The exception response factories
     * @param ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories The exception log levels
     */
    public function __construct(
        ExceptionResponseFactoryRegistry $exceptionResponseFactories,
        ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories
    ) {
        $this->exceptionResponseFactories = $exceptionResponseFactories;
        $this->logLevelFactories = $exceptionLogLevelFactories;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        $appBuilder->getComponentBuilder(MiddlewareBuilder::class)
            ->withGlobalMiddleware(new MiddlewareBinding(ExceptionHandler::class));
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
        $this->logLevelFactories->registerFactory($exceptionType, $logLevelFactory);

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
        $this->exceptionResponseFactories->registerFactory($exceptionType, $responseFactory);

        return $this;
    }
}
