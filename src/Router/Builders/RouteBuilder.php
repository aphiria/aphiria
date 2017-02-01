<?php
namespace Opulence\Router\Builders;

use Closure;
use LogicException;
use Opulence\Router\IRouteTemplate;
use Opulence\Router\Middleware\MiddlewareMetadata;
use Opulence\Router\Route;
use Opulence\Router\RouteAction;

/**
 * Defines the route builder
 */
class RouteBuilder
{
    /** @var array The list of HTTP methods to match on */
    private $httpMethods = [];
    /** @var Closure The action the route takes */
    private $action = null;
    /** @var bool Whether or not the route is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var IRouteTemplate The route template */
    private $routeTemplate = null;
    /** @var MiddlewareMetadata[] The list of middleware metadata on this route */
    private $middlewareMetadata = [];
    /** @var string|null The name of this route */
    private $name = null;

    public function __construct(
        array $httpMethods,
        IRouteTemplate $routeTemplate,
        bool $isHttpsOnly = false
    ) {
        $this->httpMethods = $httpMethods;
        $this->routeTemplate = $routeTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
    }

    public function build() : Route
    {
        if ($this->action === null) {
            throw new LogicException('No controller specified for route');
        }

        return new Route(
            $this->httpMethods, 
            $this->action, 
            $this->routeTemplate, 
            $this->isHttpsOnly, 
            $this->middlewareMetadata,
            $this->name
        );
    }

    public function toClosure(Closure $controller) : self
    {
        $this->action = new RouteAction(null, null, $controller);

        return $this;
    }

    public function toMethod(string $controllerClassName, string $controllerMethodName) : self
    {
        $this->action = new RouteAction($controllerClassName, $controllerMethodName);

        return $this;
    }

    /**
     * Adds many middleware metadata to the route
     * 
     * @param MiddlewareMetadata[]|string $middlewareMetadata The list of middleware metadata to add, or a single class name without properties
     * @return self For chaining
     * @throws InvalidArgumentException Thrown if the middleware metadata is not the correct type
     */
    public function withManyMiddleware(array $middlewareMetadata) : self
    {
        foreach ($middlewareMetadata as $singleMiddlewareMetadata) {
            if (is_string($singleMiddlewareMetadata)) {
                $this->middlewareMetadata[] = new MiddlewareMetadata($singleMiddlewareMetadata);
            } elseif ($singleMiddlewareMetadata instanceof MiddlewareMetadata) {
                $this->middlewareMetadata[] = $singleMiddlewareMetadata;
            } else {
                throw new InvalidArgumentException(
                    'Middleware metadata must either be a string or an instance of ' . MiddlewareMetadata::class
                );
            }
        }

        return $this;
    }

    public function withMiddleware(string $middlewareClassName, array $middlewareProperties = []) : self
    {
        $this->middlewareMetadata[] = new MiddlewareMetadata($middlewareClassName, $middlewareProperties);

        return $this;
    }

    public function withName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }
}
