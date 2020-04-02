<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Builders;

use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Closure;
use InvalidArgumentException;

/**
 * Defines the route builder registrant that uses route builders to register routes
 */
final class RouteBuilderRouteRegistrant implements IRouteRegistrant
{
    /** @var Closure[] The list of closures that take in a RouteBuilder instance and register routes */
    private array $routeBuilderClosures;

    /**
     * @param Closure[]|Closure $routeBuilderClosures The list of closures that take in a RouteBuilder instance and register routes
     * @throws InvalidArgumentException Thrown if the parameter was not a Closure nor list of Closures
     */
    public function __construct($routeBuilderClosures)
    {
        if (\is_array($routeBuilderClosures)) {
            $this->routeBuilderClosures = $routeBuilderClosures;
        } elseif ($routeBuilderClosures instanceof Closure) {
            $this->routeBuilderClosures = [$routeBuilderClosures];
        } else {
            throw new InvalidArgumentException('Closures must be an instance of ' . Closure::class . ' or an array of Closures');
        }
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        $routeBuilders = new RouteBuilderRegistry();

        foreach ($this->routeBuilderClosures as $closure) {
            $closure($routeBuilders);
        }

        $routes->addMany($routeBuilders->buildAll());
    }
}
