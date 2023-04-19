<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Builders;

use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use Closure;

/**
 * Defines the route collection builder
 */
final class RouteCollectionBuilder
{
    /** @var list<RouteGroupOptions> The stack of route group options */
    private array $groupOptionsStack = [];
    /** @var list<RouteBuilder> The list of registered route builders */
    private array $routeBuilders = [];

    /**
     * Builds a route collection from all the route builders
     *
     * @return RouteCollection The collection of built routes
     */
    public function build(): RouteCollection
    {
        $builtRoutes = [];

        foreach ($this->routeBuilders as $routeBuilder) {
            $builtRoutes[] = $routeBuilder->build();
        }

        return new RouteCollection($builtRoutes);
    }

    /**
     * Creates a route builder with the DELETE HTTP method
     *
     * @param string $path The path template
     * @param string|null $host The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function delete(string $path, string $host = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('DELETE', $path, $host, $isHttpsOnly);
    }

    /**
     * Creates a route builder with the GET HTTP method
     *
     * @param string $path The path template
     * @param string|null $host The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function get(string $path, string $host = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('GET', $path, $host, $isHttpsOnly);
    }

    /**
     * Creates a group of routes that share similar options
     *
     * @param RouteGroupOptions $groupOptions The list of options shared by all routes in the group
     * @param Closure(RouteCollectionBuilder): void $callback The callback that accepts an instance of this class
     */
    public function group(RouteGroupOptions $groupOptions, Closure $callback): void
    {
        $this->groupOptionsStack[] = $groupOptions;
        $callback($this);
        \array_pop($this->groupOptionsStack);
    }

    /**
     * Creates a route builder with the OPTIONS HTTP method
     *
     * @param string $path The path template
     * @param string|null $host The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function options(string $path, string $host = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('OPTIONS', $path, $host, $isHttpsOnly);
    }

    /**
     * Creates a route builder with the PATCH HTTP method
     *
     * @param string $path The path template
     * @param string|null $host The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function patch(string $path, string $host = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('PATCH', $path, $host, $isHttpsOnly);
    }

    /**
     * Creates a route builder with the POST HTTP method
     *
     * @param string $path The path template
     * @param string|null $host The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function post(string $path, string $host = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('POST', $path, $host, $isHttpsOnly);
    }

    /**
     * Creates a route builder with the PUT HTTP method
     *
     * @param string $path The path template
     * @param string|null $host The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function put(string $path, string $host = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('PUT', $path, $host, $isHttpsOnly);
    }

    /**
     * Creates a route builder with some values already set
     *
     * @param list<string>|string $httpMethods The HTTP method or list of methods the route uses
     * @param string $path The path template
     * @param string|null $host The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function route(
        string|array $httpMethods,
        string $path,
        string $host = null,
        bool $isHttpsOnly = false
    ): RouteBuilder {
        $this->applyGroupRouteTemplates($path, $host, $isHttpsOnly);
        $routeBuilder = new RouteBuilder(
            (array)$httpMethods,
            new UriTemplate($path, $host, $isHttpsOnly)
        );
        $this->applyGroupConstraints($routeBuilder);
        $this->applyGroupMiddleware($routeBuilder);
        $this->applyGroupParameters($routeBuilder);
        $this->routeBuilders[] = $routeBuilder;

        return $routeBuilder;
    }

    /**
     * Applies a group's constraints to the input route builder
     *
     * @param RouteBuilder $routeBuilder The route builder to bind constraints to
     */
    private function applyGroupConstraints(RouteBuilder $routeBuilder): void
    {
        $groupConstraints = [];

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupConstraints = [...$groupConstraints, ...$groupOptions->constraints];
        }

        $routeBuilder->withManyConstraints($groupConstraints);
    }

    /**
     * Applies a group's middleware to the input route builder
     *
     * @param RouteBuilder $routeBuilder The route builder to bind middleware to
     */
    private function applyGroupMiddleware(RouteBuilder $routeBuilder): void
    {
        $groupMiddlewareBindings = [];

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupMiddlewareBindings = [...$groupMiddlewareBindings, ...$groupOptions->middlewareBindings];
        }

        $routeBuilder->withManyMiddleware($groupMiddlewareBindings);
    }

    /**
     * Applies a group's attributes to the input route builder
     *
     * @param RouteBuilder $routeBuilder The route builder to bind attributes to
     */
    private function applyGroupParameters(RouteBuilder $routeBuilder): void
    {
        $groupParameters = [];

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupParameters = [...$groupParameters, ...$groupOptions->parameters];
        }

        $routeBuilder->withManyParameters($groupParameters);
    }

    /**
     * Applies all the group options to a route
     *
     * @param string $path The path template to apply settings to
     * @param string|null $host The host template to apply settings to
     * @param bool $isHttpsOnly Whether or not the group is HTTPS-only
     */
    private function applyGroupRouteTemplates(
        string &$path,
        string &$host = null,
        bool &$isHttpsOnly = false
    ): void {
        $groupPath = '';
        $groupHost = '';
        $groupIsHttpsOnly = false;

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupPath .= empty($groupOptions->path)
                ? ''
                : '/' . \ltrim($groupOptions->path, '/');
            $groupHost = empty($groupOptions->host)
                ? ''
                : \rtrim($groupOptions->host, '.') . (empty($groupHost) ? '' : '.' . $groupHost);
            $groupIsHttpsOnly = $groupIsHttpsOnly || $groupOptions->isHttpsOnly;
        }

        if (!empty($groupPath)) {
            // Remove any trailing slash in the case that the route path was empty
            $path = \rtrim($groupPath . '/' . \ltrim($path, '/'), '/');
        }

        $host = \rtrim($host ?? '', '.');

        if (!empty($groupHost)) {
            $host .= '.' . $groupHost;
        }

        $isHttpsOnly = $isHttpsOnly || $groupIsHttpsOnly;
    }
}
