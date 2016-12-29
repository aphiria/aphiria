<?php
namespace Opulence\Router;

use Closure;
use LogicException;
use Opulence\Router\Dispatchers\IRouteDispatcherFactory;

/**
 * Defines the route builder
 */
class RouteBuilder
{
    /** @var IRouteDispatcherFactory The route dispatcher factory */
    private $routeDispatcherFactory = null;
    /** @var array The list of HTTP methods to match on */
    private $httpMethods = [];
    /** @var bool Whether or not the route is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var RouteTemplate The path route template */
    private $pathTemplate = null;
    /** @var RouteTemplate The host route template */
    private $hostTemplate = null;
    /** @var string The name of the controller class to map to */
    private $controllerClassName = "";
    /** @var string The name of the method in the controller class to map to */
    private $controllerMethodName = "";
    /** @var Closure The callback to map to */
    private $closureController = null;
    /** @var array The list of middleware on this route */
    private $middleware = [];
    /** @var string|null The name of this route */
    private $name = null;
    
    public function __construct(
            IRouteDispatcherFactory $routeDispatcherFactory, 
            array $httpMethods, 
            RouteTemplate $pathTemplate, 
            RouteTemplate $hostTemplate = null, 
            bool $isHttpsOnly = false
    ) {
        $this->routeDispatcherFactory = $routeDispatcherFactory;
        $this->httpMethods = $httpMethods;
        $this->pathTemplate = $pathTemplate;
        $this->hostTemplate = $hostTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
    }
    
    public function build() : Route
    {
        if ($this->closureController instanceof Closure) {
            $controller = $this->routeDispatcherFactory->createRouteDispatcherFromClosure($this->closureController, $this->middleware);
        } elseif (strlen($this->controllerClassName) > 0 && strlen($this->controllerMethodName) > 0) {
            $controller = $this->routeDispatcherFactory->createRouteDispatcherFromController($this->controllerClassName, $this->controllerMethodName, $this->middleware);
        } else {
            throw new LogicException("No controller set");
        }
        
        // Todo: Need to create constraints
        return new Route($controller, [], $this->pathTemplate, $this->hostTemplate, $this->isHttpsOnly, $this->name);
    }
    
    public function toClosure(Closure $controller) : self
    {
        $this->closureController = $controller;
        
        return $this;
    }
    
    public function toMethod(string $controllerClassName, string $controllerMethodName) : self
    {
        $this->controllerClassName = $controllerClassName;
        $this->controllerMethodName = $controllerMethodName;
        
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