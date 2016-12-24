<?php
namespace Opulence\Router;

/**
 * Defines the route group options
 */
class RouteGroupOptions
{
    private $rawPathPrefix = "";
    private $rawHost = "";
    private $middleware = [];
    private $isHttpsOnly = false;
    
    public function __construct(string $rawPathPrefix, string $rawHost, bool $isHttpsOnly, array $middleware = [])
    {
        $this->rawPathPrefix = $rawPathPrefix;
        $this->rawHost = $rawHost;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->middleware = $middleware;
    }
    
    public function getRawHost() : string
    {
        return $this->rawHost;
    }
    
    public function getRawPathPrefix() : string
    {
        return $this->rawPathPrefix;
    }
    
    public function getMiddleware() : array
    {
        return $this->middleware;
    }
    
    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
}