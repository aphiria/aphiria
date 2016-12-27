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
    private $pathTemplate = "";
    /** @var string The raw host */
    private $hostTemplate = "";
    /** @var bool Whether or not this route is HTTPS-only */
    private $isHttpsOnly = false;
    
    public function __construct($methods, string $pathTemplate, string $hostTemplate = "", bool $isHttpsOnly = false)
    {
        $this->methods = (array)$methods;
        
        if (count($this->methods) == 0) {
            throw new InvalidArgumentException("Must specify at least one method");
        }
        
        $this->pathTemplate = $pathTemplate;
        $this->hostTemplate = $hostTemplate;
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
     * Gets the host template
     * 
     * @return The host template
     */
    public function getHostTemplate() : string
    {
        return $this->hostTemplate;
    }
    
    /**
     * Gets the path template
     * 
     * @return The path template
     */
    public function getPathTemplate() : string
    {
        return $this->pathTemplate;
    }
    
    /**
     * Gets whether or not the route is HTTPS-only
     * 
     * @return bool True if the route is HTTPS-only, otherwise false
     */
    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
}