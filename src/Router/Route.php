<?php
namespace Opulence\Router;

use Closure;
use Opulence\Router\Dispatchers\RouteAction;

/**
 * Defines an HTTP route
 */
class Route
{
    /** @var array The list of HTTP methods this route handles */
    private $httpMethods = [];
    /** @var RouteAction The action this route performs */
    private $action = null;
    /** @var string|null The name of this route */
    private $name = null;
    /** @var IRouteTemplate The route template */
    private $routeTemplate = null;
    /** @var bool Whether or not this route is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var array The list of any middleware on this route */
    private $middleware = [];

    public function __construct(
        $httpMethods,
        RouteAction $action,
        IRouteTemplate $routeTemplate,
        bool $isHttpsOnly = false,
        array $middleware = [],
        string $name = null
    ) {
        $this->httpMethods = (array)$httpMethods;
        $this->action = $action;
        $this->routeTemplate = $routeTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->middleware = $middleware;
        $this->name = $name;
    }

    public function getAction() : RouteAction
    {
        return $this->action;
    }

    public function getHttpMethods() : array
    {
        return $this->httpMethods;
    }

    public function getMiddleware() : array
    {
        return $this->middleware;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getRouteTemplate() : IRouteTemplate
    {
        return $this->routeTemplate;
    }

    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
}