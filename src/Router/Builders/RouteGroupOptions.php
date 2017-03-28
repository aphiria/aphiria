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
    /** @var string The host template that applies to the entire group */
    private $hostTemplate = '';
    /** @var MiddlewareBinding[] The list of middleware bindings that applies to the entire group */
    private $middlewareBindings = [];
    /** @var bool Whether or not the entire group is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var array The list of header values to match on for the entire group */
    private $headersToMatch = [];

    /**
     * @param string $pathTemplate The path template that applies to the entire group
     * @param string $hostTemplate The host template that applies to the entire group
     * @param bool $isHttpsOnly Whether or not the entire group is HTTPS-only
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings that applies to the entire group
     * @param array $headersToMatch The list of header values to match on for the entire group
     */
    public function __construct(
        string $pathTemplate,
        string $hostTemplate,
        bool $isHttpsOnly,
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
     * @return array
     */
    public function getHeadersToMatch() : array
    {
        return $this->headersToMatch;
    }

    /**
     * @return string
     */
    public function getHostTemplate() : string
    {
        return $this->hostTemplate;
    }

    /**
     * @return MiddlewareBinding[]
     */
    public function getMiddlewareBindings() : array
    {
        return $this->middlewareBindings;
    }

    /**
     * @return string
     */
    public function getPathTemplate() : string
    {
        return $this->pathTemplate;
    }

    /**
     * @return bool
     */
    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
}
