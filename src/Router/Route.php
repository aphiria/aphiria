<?php
namespace Opulence\Router;

use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\UriTemplates\UriTemplate;

/**
 * Defines an HTTP route
 */
class Route
{
    /** @var array The list of HTTP methods this route handles */
    private $httpMethods = [];
    /** @var UriTemplate The URI template */
    private $uriTemplate = null;
    /** @var array The list of header values to match on */
    private $headersToMatch = [];
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
     * @param array $headersToMatch The list of header values to match on
     */
    public function __construct(
        $httpMethods,
        UriTemplate $uriTemplate,
        RouteAction $action,
        array $middlewareBindings = [],
        string $name = null,
        array $headersToMatch = []
    ) {
        $this->httpMethods = (array)$httpMethods;
        $this->uriTemplate = $uriTemplate;
        $this->action = $action;
        $this->middlewareBindings = $middlewareBindings;
        $this->name = $name;
        $this->headersToMatch = $headersToMatch;
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
     * Gets the mapping of header names => values to match on
     * 
     * @return array The mapping of header names => values
     */
    public function getHeadersToMatch() : array
    {
        return $this->headersToMatch;
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
