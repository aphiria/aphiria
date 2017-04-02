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
    /** @var IUriTemplate The URI template */
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
        IUriTemplate $uriTemplate,
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
     * @return RouteAction
     */
    public function getAction() : RouteAction
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getHeadersToMatch() : array
    {
        return $this->headersToMatch;
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
