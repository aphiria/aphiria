<?php
namespace Opulence\Router;

use Closure;
use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Serializer;

/**
 * Defines an HTTP route
 */
class Route
{
    /** @var array The list of HTTP methods this route handles */
    private $httpMethods = [];
    /** @var Closure The action this route performs */
    private $action = null;
    /** @var string|null The name of this route */
    private $name = null;
    /** @var IRouteTemplate The path route template */
    private $pathTemplate = null;
    /** @var IRouteTemplate The host route template */
    private $hostTemplate = null;
    /** @var bool Whether or not this route is HTTPS-only */
    private $isHttpsOnly = false;
    /** @var array The list of any middleware on this route */
    private $middleware = [];
    /** @var string The serialized action */
    private $serializedAction = "";

    public function __construct(
        $httpMethods,
        Closure $action,
        IRouteTemplate $pathTemplate,
        bool $isHttpsOnly = false,
        array $middleware = [],
        IRouteTemplate $hostTemplate = null,
        string $name = null
    ) {
        $this->httpMethods = (array)$httpMethods;
        $this->action = $action;
        $this->pathTemplate = $pathTemplate;
        $this->isHttpsOnly = $isHttpsOnly;
        $this->middleware = $middleware;
        $this->hostTemplate = $hostTemplate;
        $this->name = $name;
    }

    /**
     * Serializes the actions
     *
     * @return array The list of properties to store
     */
    public function __sleep() : array
    {
        $serializer = new Serializer(new AstAnalyzer());
        $this->serializedAction = $serializer->serialize($this->action);
        $this->action = null;

        return array_keys(get_object_vars($this));
    }

    /**
     * Deserializes the actions
     */
    public function __wakeup()
    {
        $serializer = new Serializer(new AstAnalyzer());
        $this->action = $serializer->unserialize($this->serializedAction);
        $this->serializedAction = "";
    }

    public function getAction() : Closure
    {
        return $this->action;
    }

    public function getHostTemplate() : ?IRouteTemplate
    {
        return $this->hostTemplate;
    }

    public function getHttpMethods() : array
    {
        return $this->httpMethods;
    }

    public function getMiddleware() : array
    {
        return $this->middleware;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getPathTemplate() : IRouteTemplate
    {
        return $this->pathTemplate;
    }

    public function isHttpsOnly() : bool
    {
        return $this->isHttpsOnly;
    }
}