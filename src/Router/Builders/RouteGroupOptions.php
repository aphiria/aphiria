<?php
namespace Opulence\Router\Builders;

use Opulence\Router\Middleware\MiddlewareMetadata;

/**
 * Defines the route group options
 */
class RouteGroupOptions
{
    /** @var string The path template that applies to the entire group */
    private $pathTemplate = '';
    /** @var string The host template that applies to the entire group */
    private $hostTemplate = '';
    /** @var MiddlewareMetadata[] The list of middleware metadata that applies to the entire group */
    private $middlewareMetadata = [];
    /** @var bool Whether or not the entire group is HTTPS-only */
    private $isHttpsOnly = false;

    /**
     * @param string $pathTemplate The path template that applies to the entire group
     * @param string $hostTemplate The host template that applies to the entire group
     * @param bool $isHttpsOnly Whether or not the entire group is HTTPS-only
     * @param MiddlewareMetadata[] $middlewareMetadata The list of middleware metadata that applies to the entire group
     */
    public function __construct(
        string $pathTemplate, 
        string $hostTemplate, 
        bool $isHttpsOnly, 
        array $middlewareMetadata = []
    ) {
        $this->pathTemplate = $pathTemplate;
        $this->hostTemplate = $hostTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->middlewareMetadata = $middlewareMetadata;
    }

    public function getHostTemplate() : string
    {
        return $this->hostTemplate;
    }

    public function getMiddlewareMetadata() : array
    {
        return $this->middlewareMetadata;
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
