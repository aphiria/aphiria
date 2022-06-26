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
use Aphiria\Framework\Api\ApiApplication;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponseWriter;
use RuntimeException;

/**
 * Defines the application builder for API applications
 */
final class ApiApplicationBuilder extends ApplicationBuilder
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
    public function build(): ApiApplication
    {
        $this->configureModules();
        $this->buildComponents();

        try {
            $apiGateway = new ApiGateway(
                $this->container->for(
                    new TargetedContext(ApiGateway::class),
                    fn (IContainer $container) => $container->resolve(IRequestHandler::class)
                ),
                $this->container->for(
                    new TargetedContext(ApiGateway::class),
                    fn (IContainer $container) => $container->resolve(MiddlewareCollection::class)
                )
            );

            $this->container->bindInstance(IRequestHandler::class, $apiGateway);

            return new ApiApplication($apiGateway, $this->container->resolve(IRequest::class), $this->container->resolve(IResponseWriter::class));
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }
    }
}
