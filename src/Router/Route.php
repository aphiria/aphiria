<?php
namespace Opulence\Router;

use Opulence\Router\Middleware\MiddlewareMetadata;

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
    /** @var MiddlewareMetadata[] The list of any middleware metadata on this route */
    private $middlewareMetadata = [];

    public function __construct(
        $httpMethods,
        RouteAction $action,
        IRouteTemplate $routeTemplate,
        bool $isHttpsOnly = false,
        array $middlewareMetadata = [],
        string $name = null
    ) {
        $this->httpMethods = (array)$httpMethods;
        $this->action = $action;
        $this->routeTemplate = $routeTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->middlewareMetadata = $middlewareMetadata;
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

    /**
     * @return MiddlewareMetadata[]
     */
    public function getMiddlewareMetadata() : array
    {
        return $this->middlewareMetadata;
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
