<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Routing\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Routing\Attributes\AttributeRouteRegistrant;
use Aphiria\Routing\Caching\FileRouteCache;
use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\Matchers\IRouteMatcher;
use Aphiria\Routing\Matchers\TrieRouteMatcher;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Routing\UriTemplates\AstRouteUriFactory;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\FileTrieCache;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\ITrieCache;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieFactory;
use Aphiria\Routing\UriTemplates\IRouteUriFactory;

/**
 * Defines the routing binder
 */
final class RoutingBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if the config is missing values
     */
    public function bind(IContainer $container): void
    {
        $routes = new RouteCollection();
        $container->bindInstance(RouteCollection::class, $routes);
        $trieCache = new FileTrieCache(GlobalConfiguration::getString('aphiria.routing.trieCachePath'));
        $routeCache = new FileRouteCache(GlobalConfiguration::getString('aphiria.routing.routeCachePath'));
        $container->bindInstance(IRouteCache::class, $routeCache);
        $container->bindInstance(ITrieCache::class, $trieCache);

        if (getenv('APP_ENV') === 'production') {
            $routeRegistrants = new RouteRegistrantCollection($routeCache);
        } else {
            $routeRegistrants = new RouteRegistrantCollection();
        }

        $container->bindInstance(RouteRegistrantCollection::class, $routeRegistrants);

        // Bind as a factory so that our app builders can register all routes prior to the routes being built
        $container->bindFactory(
            [IRouteMatcher::class, TrieRouteMatcher::class],
            static function () use ($routes, $routeRegistrants, $trieCache) {
                $routeRegistrants->registerRoutes($routes);

                if (\getenv('APP_ENV') === 'production') {
                    $trieFactory = new TrieFactory($routes, $trieCache);
                } else {
                    $trieFactory = new TrieFactory($routes);
                }

                return new TrieRouteMatcher(($trieFactory)->createTrie());
            },
            true
        );

        $container->bindInstance(IRouteUriFactory::class, new AstRouteUriFactory($routes));

        // Register some route attribute dependencies
        /** @var string[] $attributePaths */
        $attributePaths = GlobalConfiguration::getArray('aphiria.routing.attributePaths');
        $routeAttributeRegistrant = new AttributeRouteRegistrant($attributePaths);
        $container->bindInstance(AttributeRouteRegistrant::class, $routeAttributeRegistrant);
    }
}
