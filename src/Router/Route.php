<?php
namespace Opulence\Router;

use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\UriTemplates\IUriTemplate;

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
    /** @var IUriTemplate The URI template */
    private $uriTemplate = null;
    /** @var MiddlewareBinding[] The list of any middleware bindings on this route */
    private $middlewareBindings = [];

    /**
     * @param array|string $httpMethods The HTTP method or list of methods this route matches on
     * @param RouteAction $action The action this route takes
     * @param IRouteTemplate $uriTemplate The URI template for this route
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings
     * @param string|null $name The name of this route
     */
    public function __construct(
        $httpMethods,
        RouteAction $action,
        IUriTemplate $uriTemplate,
        array $middlewareBindings = [],
        string $name = null
    ) {
        $this->httpMethods = (array)$httpMethods;
        $this->action = $action;
        $this->uriTemplate = $uriTemplate;
        $this->middlewareBindings = $middlewareBindings;
        $this->name = $name;
    }

    /**
     * @return RouteAction
     */
    public function getAction() : RouteAction
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getHttpMethods() : array
    {
        return $this->httpMethods;
    }

    /**
     * @return MiddlewareBinding[]
     */
    public function getMiddlewareBindings() : array
    {
        return $this->middlewareBindings;
    }

    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @return IUriTemplate
     */
    public function getUriTemplate() : IUriTemplate
    {
        return $this->uriTemplate;
    }
}
