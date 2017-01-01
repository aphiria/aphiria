<?php
namespace Opulence\Router;

use Closure;

/**
 * Defines an HTTP route
 */
class Route
{
    /** @var array The list of HTTP methods this route handles */
    private $httpMethods = [];
    /** @var Closure The action this route performs */
    private $action = null;
    /** @var string|null The name of this route */
    private $name = null;
    /** @var IRouteTemplate The path route template */
    private $pathTemplate = null;
    /** @var IRouteTemplate The host route template */
    private $hostTemplate = null;
    /** @var bool Whether or not this route is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var array The list of any middleware on this route */
    private $middleware = [];

    public function __construct(
        $httpMethods,
        Closure $action,
        IRouteTemplate $pathTemplate,
        bool $isHttpsOnly,
        array $middleware = [],
        IRouteTemplate $hostTemplate = null,
        string $name = null
    ) {
        $this->httpMethods = (array)$httpMethods;
        $this->action = $action;
        $this->pathTemplate = $pathTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->middleware = $middleware;
        $this->hostTemplate = $hostTemplate;
        $this->name = $name;
    }

    public function getAction() : Closure
    {
        return $this->action;
    }

    public function getHostTemplate() : ?IRouteTemplate
    {
        return $this->hostTemplate;
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

    public function getPathTemplate() : IRouteTemplate
    {
        return $this->pathTemplate;
    }

    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
}