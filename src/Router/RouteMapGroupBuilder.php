<?php
namespace Opulence\Router;

/**
 * Defines the group route map builder
 */
class RouteMapGroupBuilder
{
    private $groupCallable = null;
    private $middleware = [];
    private $rawHost = "";
    private $rawPathPrefix = "";
    
    public function __construct(callable $groupCallable)
    {
        $this->groupCallable = $groupCallable;
    }
    
    public function build()
    {
        // Todo: I have to somehow apply the settings in the builder to everything inside the callable
        // Todo: What should this method return?
    }
    
    public function withMiddleware($middleware) : self
    {
        $this->middleware = array_merge($this->middleware, (array)$middleware);
        
        return $this;
    }
    
    public function withRawHost(string $rawHost) : self
    {
        $this->rawHost = $rawHost;
        
        return $this;
    }
    
    public function withRawPathPrefix(string $rawPathPrefix) : self
    {
        $this->rawPathPrefix = $rawPathPrefix;
        
        return $this;
    }
}