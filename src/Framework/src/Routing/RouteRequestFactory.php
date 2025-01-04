<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Routing;

use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Uri;
use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\AstRouteUriFactory;
use Aphiria\Routing\UriTemplates\IRouteUriFactory;
use Aphiria\Routing\UriTemplates\RouteUriCreationException;
use InvalidArgumentException;
use OutOfBoundsException;
use ReflectionException;
use ReflectionMethod;

final class RouteRequestFactory implements IRouteRequestFactory
{
    /** @var IRouteUriFactory The route URI factory */
    private readonly IRouteUriFactory $routeUriFactory;

    /**
     * @param RouteCollection $routes The list of routes
     * @param IRouteUriFactory|null $routeUriFactory The route URI factory to use, or null if using the default one
     */
    public function __construct(private readonly RouteCollection $routes, ?IRouteUriFactory $routeUriFactory = null)
    {
        $this->routeUriFactory = $routeUriFactory ?? new AstRouteUriFactory($this->routes);
    }

    /**
     * @inheritdoc
     */
    public function createRouteRequest(string $routeName, array $routeVariables = [], ?string $method = null): IRequest
    {
        if (($route = $this->routes->getNamedRoute($routeName)) === null) {
            throw new OutOfBoundsException("Route \"$routeName\" does not exist");
        }

        if ($method === null) {
            $supportedMethods = [];

            foreach ($route->constraints as $constraint) {
                if ($constraint instanceof HttpMethodRouteConstraint) {
                    foreach ($constraint->allowedMethods as $method) {
                        $supportedMethods[] = $method;
                    }
                }
            }

            if (\count($supportedMethods) === 1) {
                $method = $supportedMethods[0];
            } elseif (\count($supportedMethods) === 2 && \in_array('GET', $supportedMethods) && \in_array('HEAD', $supportedMethods)) {
                $method = 'GET';
            } else {
                throw new InvalidArgumentException("Method must be specified if there is more than one supported method - route \"$routeName\" supports methods " . \implode(', ', $supportedMethods));
            }
        }

        try {
            $reflectionMethod = new ReflectionMethod($route->action->className, $route->action->methodName);
        } catch (ReflectionException $ex) {
            throw new RouteRequestCreationException("Failed to reflect {$route->action->className}::{$route->action->methodName}", 0, $ex);
        }

        $headers = new Headers();

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            if (\count($headerAttributes = $parameter->getAttributes(Header::class)) === 1) {
                $parameterName = $headerAttributes[0]->newInstance()->name ?? $parameterName;
                $value = null;

                if (\array_key_exists($parameterName, $routeVariables)) {
                    $value = $routeVariables[$parameterName];
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $value = $parameter->getDefaultValue();
                } else {
                    throw new RouteRequestCreationException("Failed to create route request because the parameter \"$parameterName\" is required but not provided");
                }

                $headers->add($parameterName, $value);
            }
        }

        try {
            return new Request($method, new Uri($this->routeUriFactory->createRouteUri($routeName, $routeVariables)), $headers);
        } catch (RouteUriCreationException $ex) {
            throw new RouteRequestCreationException('Failed to create route request', 0, $ex);
        }
    }
}
