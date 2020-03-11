<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Builders;

use Aphiria\Api\App;
use Aphiria\Application\Builders\ApplicationBuilder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use RuntimeException;

/**
 * Defines the application builder for API applications
 */
final class ApiApplicationBuilder extends ApplicationBuilder
{
    /** @var IContainer The DI container */
    private IContainer $container;

    /**
     * @param IContainer $container The DI container
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function build(): IRequestHandler
    {
        $this->buildModules();
        $this->buildComponents();

        try {
            $apiApp = new App(
                $this->container->for(App::class, fn (IContainer $container) => $container->resolve(IRequestHandler::class)),
                $this->container->for(App::class, fn (IContainer $container) => $container->resolve(MiddlewareCollection::class))
            );
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }

        $this->container->bindInstance(IRequestHandler::class, $apiApp);

        return $apiApp;
    }
}
