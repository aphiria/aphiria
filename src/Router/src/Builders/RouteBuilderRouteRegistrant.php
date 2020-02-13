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
    /** @var Closure[] The list of callbacks that take in a RouteBuilder instance and register routes */
    private array $routeBuilderCallbacks;

    /**
     * @param Closure[]|Closure $routeBuilderCallbacks The list of callbacks that take in a RouteBuilder instance and register routes
     * @throws InvalidArgumentException Thrown if the callbacks were not the a Closure nor list of Closures
     */
    public function __construct($routeBuilderCallbacks)
    {
        if (is_array($routeBuilderCallbacks)) {
            $this->routeBuilderCallbacks = $routeBuilderCallbacks;
        } elseif ($routeBuilderCallbacks instanceof Closure) {
            $this->routeBuilderCallbacks = [$routeBuilderCallbacks];
        } else {
            throw new InvalidArgumentException('Callbacks must be an instance of ' . Closure::class . ' or an array of Closures');
        }
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        $routeBuilders = new RouteBuilderRegistry();

        foreach ($this->routeBuilderCallbacks as $callback) {
            $callback($routeBuilders);
        }

        $routes->addMany($routeBuilders->buildAll());
    }
}
