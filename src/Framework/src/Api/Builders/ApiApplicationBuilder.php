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
use Aphiria\Application\IBootstrapper;
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
     * @param IBootstrapper[] $bootstrappers The list of bootstrappers to run to bootstrap the application
     */
    public function __construct(IContainer $container, array $bootstrappers)
    {
        parent::__construct($bootstrappers);

        $this->container = $container;
        // TODO: Should bootstrap happen here, or outside?  I don't want to confuse devs with bootstrap() and build() methods in the same class.
        $this->bootstrap();
    }

    /**
     * @inheritdoc
     */
    public function build(): IRequestHandler
    {
        $this->buildModules();
        $this->initializeComponents();

        /** @var IRequestHandler $router */
        $router = null;
        $this->container->for(App::class, static function (IContainer $container) use (&$router) {
            if (!$container->tryResolve(IRequestHandler::class, $router)) {
                throw new RuntimeException('No ' . IRequestHandler::class . ' router bound to the container');
            }
        });

        try {
            $apiApp = new App($router, $this->container->resolve(MiddlewareCollection::class));
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }

        $this->container->bindInstance(IRequestHandler::class, $apiApp);

        return $apiApp;
    }
}
