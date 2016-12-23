<?php
namespace Opulence\Router;

use InvalidArgumentException;

/**
 * Defines a route
 */
class Route 
{
    /** @var array The list of methods this route matches on */
    private $methods = [];
    /** @var string The raw path */
    private $rawPath = "";
    /** @var string The raw host */
    private $rawHost = "";
    /** @var bool Whether or not this route is HTTPS-only */
    private $isHttpsOnly = false;
    
    public function __construct($methods, string $rawPath, string $rawHost = "", bool $isHttpsOnly = false)
    {
        $this->methods = (array)$methods;
        
        if (count($this->methods) == 0) {
            throw new InvalidArgumentException("Must specify at least one method");
        }
        
        $this->rawPath = $rawPath;
        $this->rawHost = $rawHost;
        $this->isHttpsOnly = $isHttpsOnly;
    }
    
    /**
     * Gets the list of methods this route matches on
     * 
     * @return array The list of methods
     */
    public function getMethods() : array
    {
        return $this->methods;
    }
    
    /**
     * Gets the raw host to match on
     * 
     * @return The raw host
     */
    public function getRawHost() : string
    {
        return $this->rawPath;
    }
    
    /**
     * Gets the raw path to match on
     * 
     * @return The raw path
     */
    public function getRawPath() : string
    {
        return $this->rawPath;
    }
    
    /**
     * Gets whether or not the route is HTTPS-only
     * 
     * @return bool True if the route is HTTPS-only, otherwise false
     */
    public function isHttpsOnly() : string
    {
        return $this->isHttpsOnly;
    }
}