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

use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\UriTemplate;
use Closure;

/**
 * Defines the route builder registry
 */
final class RouteBuilderRegistry
{
    /** @var RouteBuilder[] The list of registered route builders */
    private array $routeBuilders = [];
    /** @var RouteGroupOptions[] The stack of route group options */
    private array $groupOptionsStack = [];

    /**
     * Builds all the route builders in the registry
     *
     * @return Route[] The list of routes built by this registry
     */
    public function buildAll(): array
    {
        $builtRoutes = [];

        foreach ($this->routeBuilders as $routeBuilder) {
            $builtRoutes[] = $routeBuilder->build();
        }

        return $builtRoutes;
    }

    /**
     * Creates a route builder with the DELETE HTTP method
     *
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function delete(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('DELETE', $pathTemplate, $hostTemplate, $isHttpsOnly);
    }

    /**
     * Creates a route builder with the GET HTTP method
     *
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function get(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('GET', $pathTemplate, $hostTemplate, $isHttpsOnly);
    }

    /**
     * Creates a group of routes that share similar options
     *
     * @param RouteGroupOptions $groupOptions The list of options shared by all routes in the group
     * @param Closure $callback The callback that accepts an instance of this class
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
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function options(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('OPTIONS', $pathTemplate, $hostTemplate, $isHttpsOnly);
    }

    /**
     * Creates a route builder with the PATCH HTTP method
     *
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function patch(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('PATCH', $pathTemplate, $hostTemplate, $isHttpsOnly);
    }

    /**
     * Creates a route builder with the POST HTTP method
     *
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function post(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('POST', $pathTemplate, $hostTemplate, $isHttpsOnly);
    }

    /**
     * Creates a route builder with the PUT HTTP method
     *
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function put(string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false): RouteBuilder
    {
        return $this->route('PUT', $pathTemplate, $hostTemplate, $isHttpsOnly);
    }

    /**
     * Creates a route builder with some values already set
     *
     * @param array|string $httpMethods The HTTP method or list of methods the route uses
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function route(
        $httpMethods,
        string $pathTemplate,
        string $hostTemplate = null,
        bool $isHttpsOnly = false
    ): RouteBuilder {
        $this->applyGroupRouteTemplates($pathTemplate, $hostTemplate, $isHttpsOnly);
        $routeBuilder = new RouteBuilder(
            (array)$httpMethods,
            new UriTemplate($pathTemplate, $hostTemplate, $isHttpsOnly)
        );
        $this->applyGroupConstraints($routeBuilder);
        $this->applyGroupMiddleware($routeBuilder);
        $this->applyGroupAttributes($routeBuilder);
        $this->routeBuilders[] = $routeBuilder;

        return $routeBuilder;
    }

    /**
     * Applies a group's attributes to the input route builder
     *
     * @param RouteBuilder $routeBuilder The route builder to bind attributes to
     */
    private function applyGroupAttributes(RouteBuilder $routeBuilder): void
    {
        $groupAttributes = [];

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupAttributes = \array_merge($groupAttributes, $groupOptions->attributes);
        }

        $routeBuilder->withManyAttributes($groupAttributes);
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
     * Applies all the group options to a route
     *
     * @param string $pathTemplate The path template to apply settings to
     * @param string|null $hostTemplate The host template to apply settings to
     * @param bool $isHttpsOnly Whether or not the group is HTTPS-only
     */
    private function applyGroupRouteTemplates(
        string &$pathTemplate,
        string &$hostTemplate = null,
        bool &$isHttpsOnly = false
    ): void {
        $groupPathTemplate = '';
        $groupHostTemplate = '';
        $groupIsHttpsOnly = false;

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupPathTemplate .= empty($groupOptions->pathTemplate)
                ? ''
                : '/' . ltrim($groupOptions->pathTemplate, '/');
            $groupHostTemplate = empty($groupOptions->hostTemplate)
                ? ''
                : rtrim($groupOptions->hostTemplate, '.') . (empty($groupHostTemplate) ? '' : '.' . $groupHostTemplate);
            $groupIsHttpsOnly = $groupIsHttpsOnly || $groupOptions->isHttpsOnly;
        }

        $pathTemplate = empty($groupPathTemplate)
            ? $pathTemplate
            : $groupPathTemplate . '/' . ltrim($pathTemplate, '/');
        $hostTemplate = rtrim($hostTemplate ?? '', '.');

        if (!empty($groupHostTemplate)) {
            $hostTemplate .= '.' . $groupHostTemplate;
        }

        $isHttpsOnly = $isHttpsOnly || $groupIsHttpsOnly;
    }
}
