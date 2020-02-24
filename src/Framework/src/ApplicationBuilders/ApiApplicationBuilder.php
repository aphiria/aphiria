<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\ApplicationBuilders;

use Aphiria\Api\App as ApiApp;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use RuntimeException;

/**
 * Defines the application builder for REST API applications
 */
final class ApiApplicationBuilder extends AphiriaApplicationBuilder
{
    /**
     * @inheritdoc
     */
    public function build(): IRequestHandler
    {
        $this->buildComponents();

        /** @var IRequestHandler $router */
        $router = null;
        $this->container->for(ApiApp::class, static function (IContainer $container) use (&$router) {
            if (!$container->tryResolve(IRequestHandler::class, $router)) {
                throw new RuntimeException('No ' . IRequestHandler::class . ' router bound to container');
            }
        });

        try {
            $apiApp = new ApiApp($router, $this->container->resolve(MiddlewareCollection::class));
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }

        $this->container->bindInstance(IRequestHandler::class, $apiApp);

        return $apiApp;
    }
}
