<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Framework\Routing\Bootstrappers;

use Aphiria\Configuration\Configuration;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Caching\FileRouteCache;
use Aphiria\Routing\Matchers\IRouteMatcher;
use Aphiria\Routing\Matchers\TrieRouteMatcher;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Routing\UriTemplates\AstRouteUriFactory;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\FileTrieCache;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieFactory;
use Aphiria\Routing\UriTemplates\IRouteUriFactory;
use Doctrine\Annotations\AnnotationException;

/**
 * Defines the routing bootstrapper
 */
final class RoutingBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     * @throws ConfigurationException Thrown if the config is missing values
     * @throws AnnotationException Thrown if PHP is not configured to handle scanning for annotations
     */
    public function registerBindings(IContainer $container): void
    {
        $routes = new RouteCollection();
        $container->bindInstance(RouteCollection::class, $routes);

        if (getenv('APP_ENV') === 'production') {
            $trieCache = new FileTrieCache(Configuration::getString('aphiria.routing.trieCachePath'));
            $routeCache = new FileRouteCache(Configuration::getString('aphiria.routing.routeCachePath'));
        } else {
            $trieCache = $routeCache = null;
        }

        $routeRegistrants = new RouteRegistrantCollection($routeCache);
        $container->bindInstance(RouteRegistrantCollection::class, $routeRegistrants);

        // Bind as a factory so that our app builders can register all routes prior to the routes being built
        $container->bindFactory(
            [IRouteMatcher::class, TrieRouteMatcher::class],
            function () use ($routes, $routeRegistrants, $trieCache) {
                $routeRegistrants->registerRoutes($routes);

                return new TrieRouteMatcher((new TrieFactory($routes, $trieCache))->createTrie());
            },
            true
        );

        $container->bindInstance(IRouteUriFactory::class, new AstRouteUriFactory($routes));

        // Register some route annotation dependencies
        $routeAnnotationRegistrant = new AnnotationRouteRegistrant(Configuration::getArray('aphiria.routing.annotationPaths'));
        $container->bindInstance(AnnotationRouteRegistrant::class, $routeAnnotationRegistrant);
    }
}
