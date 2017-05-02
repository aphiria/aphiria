<?php
namespace Opulence\Router\Middleware;

/**
 * Defines a middleware binding
 */
class MiddlewareBinding
{
    /** @var string The name of the middleware class */
    private $className = '';
    /** @var array The name => value mapping of parameters bound to the middleware */
    private $properties = [];

    /**
     * @param string $className The name of the middleware class
     * @param array $properties The name => value mapping of parameters bound to the middleware
     */
    public function __construct(string $className, array $properties = [])
    {
        $this->className = $className;
        $this->properties = $properties;
    }

    /**
     * Gets the name of the middleware class
     *
     * @return string The middleware class name
     */
    public function getClassName() : string
    {
        return $this->className;
    }

    /**
     * Gets the mapping of property names => values for the middleware
     *
     * @return array The mapping of property names => values
     */
    public function getProperties() : array
    {
        return $this->properties;
    }
}
