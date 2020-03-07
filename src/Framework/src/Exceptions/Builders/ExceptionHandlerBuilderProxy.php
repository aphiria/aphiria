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
use Aphiria\Application\Builders\IComponentBuilderProxy;
use Closure;

/**
 * Defines the proxy for exception handler builders so that they don't have to be instantiated until after bootstrappers are run
 */
final class ExceptionHandlerBuilderProxy extends ExceptionHandlerBuilder implements IComponentBuilderProxy
{
    /** @var Closure The factory that will generate the proxied instance */
    private Closure $instanceFactory;
    /** @var Closure[] The list of proxied calls to make on the underlying instance once it's resolved */
    private array $proxiedCalls = [];

    /**
     * @param Closure $instanceFactory The parameterless factory that will generate the proxied instance
     */
    public function __construct(Closure $instanceFactory)
    {
        $this->instanceFactory = $instanceFactory;
    }

    /**
     * @inheritdoc
     */
    public function build(IApplicationBuilder $appBuilder): void
    {
        /** @var ExceptionHandlerBuilder $instance */
        $instance = ($this->instanceFactory)();

        foreach ($this->proxiedCalls as $proxiedCall) {
            $proxiedCall($instance);
        }

        $instance->build($appBuilder);
    }

    /**
     * @inheritdoc
     */
    public function getProxiedType(): string
    {
        return ExceptionHandlerBuilder::class;
    }

    /**
     * @inheritdoc
     */
    public function withLogLevelFactory(string $exceptionType, Closure $logLevelFactory): self
    {
        $this->proxiedCalls[] = fn (ExceptionHandlerBuilder $exceptionHandlerBuilder) => $exceptionHandlerBuilder->withLogLevelFactory($exceptionType, $logLevelFactory);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withResponseFactory(string $exceptionType, Closure $responseFactory): self
    {
        $this->proxiedCalls[] = fn (ExceptionHandlerBuilder $exceptionHandlerBuilder) => $exceptionHandlerBuilder->withResponseFactory($exceptionType, $responseFactory);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withGlobalExceptionHandler(): self
    {
        $this->proxiedCalls[] = fn (ExceptionHandlerBuilder $exceptionHandlerBuilder) => $exceptionHandlerBuilder->withGlobalExceptionHandler();

        return $this;
    }
}
