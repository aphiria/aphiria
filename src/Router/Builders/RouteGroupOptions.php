<?php
namespace Opulence\Router\Builders;

use Opulence\Router\Middleware\MiddlewareBinding;

/**
 * Defines the route group options
 */
class RouteGroupOptions
{
    /** @var string The path template that applies to the entire group */
    private $pathTemplate = '';
    /** @var string|null The host template that applies to the entire group */
    private $hostTemplate = null;
    /** @var MiddlewareBinding[] The list of middleware bindings that applies to the entire group */
    private $middlewareBindings = [];
    /** @var bool Whether or not the entire group is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var array The list of header values to match on for the entire group */
    private $headersToMatch = [];

    /**
     * @param string $pathTemplate The path template that applies to the entire group
     * @param string|null $hostTemplate The host template that applies to the entire group, or null
     * @param bool $isHttpsOnly Whether or not the entire group is HTTPS-only
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings that applies to the entire group
     * @param array $headersToMatch The list of header values to match on for the entire group
     */
    public function __construct(
        string $pathTemplate,
        ?string $hostTemplate = null,
        bool $isHttpsOnly = false,
        array $middlewareBindings = [],
        array $headersToMatch = []
    ) {
        $this->pathTemplate = $pathTemplate;
        $this->hostTemplate = $hostTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->middlewareBindings = $middlewareBindings;
        $this->headersToMatch = $headersToMatch;
    }

    /**
     * Gets the header names => values to match
     * 
     * @return array The mapping of header names => values to match
     */
    public function getHeadersToMatch() : array
    {
        return $this->headersToMatch;
    }

    /**
     * Gets the host template
     * 
     * @return string|null The host template if one was defined, otherwise null
     */
    public function getHostTemplate() : ?string
    {
        return $this->hostTemplate;
    }

    /**
     * Gets the list of middleware bindings
     * 
     * @return MiddlewareBinding[] The list of middleware bindings
     */
    public function getMiddlewareBindings() : array
    {
        return $this->middlewareBindings;
    }

    /**
     * Gets the path template
     * 
     * @return string The path template
     */
    public function getPathTemplate() : string
    {
        return $this->pathTemplate;
    }

    /**
     * Gets whether or not the route group is HTTPS-only
     * 
     * @return bool True if the route group is HTTPS-only, otherwise false
     */
    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
}
