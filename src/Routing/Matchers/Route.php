<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers;

use Opulence\Routing\Matchers\Middleware\MiddlewareBinding;
use Opulence\Routing\Matchers\UriTemplates\UriTemplate;

/**
 * Defines an HTTP route
 */
class Route
{
    /** @var array The list of HTTP methods this route handles */
    private $httpMethods = [];
    /** @var UriTemplate The URI template */
    private $uriTemplate = null;
    /** @var array The mapping of custom attribute names => values */
    private $attributes = [];
    /** @var RouteAction The action this route performs */
    private $action = null;
    /** @var string|null The name of this route */
    private $name = null;
    /** @var MiddlewareBinding[] The list of any middleware bindings on this route */
    private $middlewareBindings = [];

    /**
     * @param array|string $httpMethods The HTTP method or list of methods this route matches on
     * @param IRouteTemplate $uriTemplate The URI template for this route
     * @param RouteAction $action The action this route takes
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings
     * @param string|null $name The name of this route
     * @param array $attributes The mapping of custom attribute names => values
     */
    public function __construct(
        $httpMethods,
        UriTemplate $uriTemplate,
        RouteAction $action,
        array $middlewareBindings = [],
        string $name = null,
        array $attributes = []
    ) {
        $this->httpMethods = (array)$httpMethods;
        $this->uriTemplate = $uriTemplate;
        $this->action = $action;
        $this->middlewareBindings = $middlewareBindings;
        $this->name = $name;
        $this->attributes = $attributes;
    }

    /**
     * Performs a deep clone of object properties
     */
    public function __clone()
    {
        $this->action = clone $this->action;
        $this->uriTemplate = clone $this->uriTemplate;
    }

    /**
     * Gets the action this route takes
     *
     * @return RouteAction The action this route takes
     */
    public function getAction() : RouteAction
    {
        return $this->action;
    }

    /**
     * Gets the mapping of custom attribute names => values
     *
     * @return array The mapping of attribute names => values
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * Gets the HTTP methods this route matches on
     *
     * @return array The list of HTTP methods to match on
     */
    public function getHttpMethods() : array
    {
        return $this->httpMethods;
    }

    /**
     * Gets the list of middleware bindings
     *
     * @return MiddlewareBinding[] The list of middleware bindings
     */
    public function getMiddlewareBindings() : array
    {
        return $this->middlewareBindings;
    }

    /**
     * Gets the name of this route
     *
     * @return string|null The name of this route if one was defined, other null
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Gets the URI template for this route
     *
     * @return UriTemplate The URI template
     */
    public function getUriTemplate() : UriTemplate
    {
        return $this->uriTemplate;
    }
}
