<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Builders;

use Aphiria\Api\ApiGateway;
use Aphiria\Application\Builders\ApplicationBuilder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\Framework\Api\SynchronousApiApplication;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponseWriter;
use RuntimeException;

/**
 * Defines the application builder for synchronous API applications
 */
final class SynchronousApiApplicationBuilder extends ApplicationBuilder
{
    /**
     * @param IContainer $container The DI container
     */
    public function __construct(private readonly IContainer $container)
    {
    }

    /**
     * @inheritdoc
     */
    public function build(): SynchronousApiApplication
    {
        $this->configureModules();
        $this->buildComponents();

        try {
            $apiGateway = new ApiGateway(
                $this->container->for(
                    new TargetedContext(ApiGateway::class),
                    static fn (IContainer $container): IRequestHandler => $container->resolve(IRequestHandler::class)
                ),
                $this->container->for(
                    new TargetedContext(ApiGateway::class),
                    static fn (IContainer $container): MiddlewareCollection => $container->resolve(MiddlewareCollection::class)
                )
            );
            // Bind the gateway for use in integration tests
            $this->container->bindInstance(IRequestHandler::class, $apiGateway);

            return new SynchronousApiApplication(
                $apiGateway,
                $this->container->resolve(IRequest::class),
                $this->container->resolve(IResponseWriter::class)
            );
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }
    }
}
