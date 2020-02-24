<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\Framework\ApplicationBuilders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\Serialization\Encoding\IEncoder;
use Closure;

/**
 * Defines the interface for Aphiria application builders to implement
 */
interface IAphiriaApplicationBuilder extends IApplicationBuilder
{
    /**
     * Adds bootstrappers to the bootstrapper component builder
     *
     * @param Bootstrapper|Bootstrapper[] $bootstrappers The bootstrapper or list of bootstrappers to add
     * @return self For chaining
     */
    public function withBootstrappers($bootstrappers): self;

    /**
     * Enables console command annotations
     *
     * @return self For chaining
     */
    public function withCommandAnnotations(): self;

    /**
     * Adds console commands to the command component builder
     *
     * @param Closure $callback The callback that takes in an instance of CommandRegistry to register commands to
     * @return self For chaining
     */
    public function withCommands(Closure $callback): self;

    /**
     * Adds an encoder to the encoder component builder
     *
     * @param string $class The class whose encoder we're registering
     * @param IEncoder $encoder The encoder to register
     * @return self For chaining
     */
    public function withEncoder(string $class, IEncoder $encoder): self;

    /**
     * Adds an exception response factory to the exception handler component builder
     *
     * @param string $exceptionType The type of exception whose response factory we're registering
     * @param Closure $responseFactory The factory that takes in an instance of the exception, ?IHttpRequestMessage, and INegotiatedResponseFactory and creates a response
     * @return self For chaining
     */
    public function withExceptionResponseFactory(string $exceptionType, Closure $responseFactory): self;

    /**
     * Adds global middleware bindings to the middleware component builder
     *
     * @param MiddlewareBinding|MiddlewareBinding[] $middlewareBindings The middleware binding or list of bindings to add
     * @return self For chaining
     */
    public function withGlobalMiddleware($middlewareBindings): self;

    /**
     * Adds a log level factory to the exception handler component builder
     *
     * @param string $exceptionType The exception type whose factory we're registering
     * @param Closure $logLevelFactory The factory that takes in an instance of ExceptionLogLevelFactoryRegistry to registry factories to
     * @return self For chaining
     */
    public function withLogLevelFactory(string $exceptionType, Closure $logLevelFactory): self;

    /**
     * Adds object constraints to the object constraints component builder
     *
     * @param Closure $callback The callback that takes in an instance of ObjectConstraintsRegistry to register object constraints to
     * @return self For chaining
     */
    public function withObjectConstraints(Closure $callback): self;

    /**
     * Enables routing annotations
     *
     * @return self For chaining
     */
    public function withRouteAnnotations(): self;

    /**
     * Adds routes to the router component builder
     *
     * @param Closure $callback The callback that takes in an instance of RouteBuilderRegistry to register route builders to
     * @return self For chaining
     */
    public function withRoutes(Closure $callback): self;

    /**
     * Enables Aphiria validation annotations
     *
     * @return self For chaining
     */
    public function withValidatorAnnotations(): self;
}
