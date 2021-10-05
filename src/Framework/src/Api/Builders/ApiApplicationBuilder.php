<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Builders;

use Aphiria\Api\Application;
use Aphiria\Application\Builders\ApplicationBuilder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\IRequestHandler;
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
    public function build(): IRequestHandler
    {
        $this->buildModules();
        $this->buildComponents();

        try {
            $apiApp = new Application(
                $this->container->for(
                    new TargetedContext(Application::class),
                    fn (IContainer $container) => $container->resolve(IRequestHandler::class)
                ),
                $this->container->for(
                    new TargetedContext(Application::class),
                    fn (IContainer $container) => $container->resolve(MiddlewareCollection::class)
                )
            );
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }

        $this->container->bindInstance(IRequestHandler::class, $apiApp);

        return $apiApp;
    }
}
