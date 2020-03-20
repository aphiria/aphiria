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
use Aphiria\Exceptions\Http\HttpExceptionHandler;
use Aphiria\Exceptions\LogLevelFactoryRegistry;
use Aphiria\Framework\Application\AphiriaComponents;
use Closure;

/**
 * Defines the exception handler component
 */
class ExceptionHandlerComponent implements IComponent
{
    use AphiriaComponents;

    /** @var IServiceResolver The service resolver */
    private IServiceResolver $serviceResolver;
    /** @var Closure[] The mapping of exception types to response factories */
    private array $negotiatedResponseFactories = [];
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
        /** @var HttpExceptionHandler|null $httpExceptionHandler */
        $httpExceptionHandler = null;

        if ($this->serviceResolver->tryResolve(HttpExceptionHandler::class, $httpExceptionHandler)) {
            $httpExceptionHandler->registerManyNegotiatedResponseFactories($this->negotiatedResponseFactories);
        }

        $logLevelFactories = $this->serviceResolver->resolve(LogLevelFactoryRegistry::class);
        $logLevelFactories->registerManyFactories($this->logLevelFactories);
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
     * @param Closure $responseFactory The factory that takes in an instance of the exception, the request, and the negotiated content factory, and returns a response
     * @return self For chaining
     */
    public function withNegotiatedResponseFactory(string $exceptionType, Closure $responseFactory): self
    {
        $this->negotiatedResponseFactories[$exceptionType] = $responseFactory;

        return $this;
    }
}
