<?php
namespace Opulence\Router\Builders;

/**
 * Defines the route group options
 */
class RouteGroupOptions
{
    private $pathTemplate = "";
    private $hostTemplate = "";
    private $middleware = [];
    private $isHttpsOnly = false;
    
    public function __construct(string $pathTemplate, string $hostTemplate, bool $isHttpsOnly, array $middleware = [])
    {
        $this->pathTemplate = $pathTemplate;
        $this->hostTemplate = $hostTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->middleware = $middleware;
    }
    
    public function getHostTemplate() : string
    {
        return $this->hostTemplate;
    }
    
    public function getMiddleware() : array
    {
        return $this->middleware;
    }
    
    public function getPathTemplate() : string
    {
        return $this->pathTemplate;
    }
    
    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
}