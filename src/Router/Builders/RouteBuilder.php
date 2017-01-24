<?php
namespace Opulence\Router\Builders;

use Closure;
use LogicException;
use Opulence\Router\Dispatchers\IRouteActionFactory;
use Opulence\Router\IRouteTemplate;
use Opulence\Router\Route;

/**
 * Defines the route builder
 */
class RouteBuilder
{
    /** @var IRouteActionFactory The route action factory */
    private $routeActionFactory = null;
    /** @var array The list of HTTP methods to match on */
    private $httpMethods = [];
    /** @var Closure The action the route takes */
    private $action = null;
    /** @var bool Whether or not the route is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var IRouteTemplate The route template */
    private $routeTemplate = null;
    /** @var array The list of middleware on this route */
    private $middleware = [];
    /** @var string|null The name of this route */
    private $name = null;

    public function __construct(
        IRouteActionFactory $routeActionFactory,
        array $httpMethods,
        IRouteTemplate $routeTemplate,
        bool $isHttpsOnly = false
    ) {
        $this->routeActionFactory = $routeActionFactory;
        $this->httpMethods = $httpMethods;
        $this->routeTemplate = $routeTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
    }

    public function build() : Route
    {
        if ($this->action === null) {
            throw new LogicException('No controller specified for route');
        }

        return new Route($this->httpMethods, $this->action, $this->routeTemplate, $this->isHttpsOnly, $this->middleware,
            $this->name);
    }

    public function toClosure(Closure $controller) : self
    {
        $this->action = $this->routeActionFactory->createRouteActionFromClosure($controller);

        return $this;
    }

    public function toMethod(string $controllerClassName, string $controllerMethodName) : self
    {
        $this->action = $this->routeActionFactory->createRouteActionFromController($controllerClassName,
            $controllerMethodName);

        return $this;
    }

    public function withMiddleware($middleware) : self
    {
        $this->middleware = array_merge($this->middleware, (array)$middleware);

        return $this;
    }

    public function withName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }
}
