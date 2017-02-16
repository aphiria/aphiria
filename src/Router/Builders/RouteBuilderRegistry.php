<?php
namespace Opulence\Router\Builders;

use Closure;
use Opulence\Router\RouteCollection;
use Opulence\Router\UriTemplates\Parsers\IUriTemplateParser;
use Opulence\Router\UriTemplates\Parsers\RegexUriTemplateParser;

/**
 * Defines the route builder registry
 */
class RouteBuilderRegistry
{
    /** @var RouteBuilder[] The list of registered route builders */
    private $routeBuilders = [];
    /** @var IRouteTemplateParser The route parser */
    private $routeTemplateParser = null;
    /** @var RouteGroupOptions[] The stack of route group options */
    private $groupOptionsStack = [];

    /**
     * @param IRouteTemplateParser|null $routeTemplateParser The route template parser to use
     */
    public function __construct(IUriTemplateParser $routeTemplateParser = null)
    {
        $this->routeTemplateParser = $routeTemplateParser ?? new RegexUriTemplateParser();
    }

    /**
     * Builds all the route builders in the registry
     * 
     * @return RouteCollection The list of routes built by this registry
     */
    public function buildAll() : RouteCollection
    {
        $builtRouteMaps = [];

        foreach ($this->routeBuilders as $routeMapBuilder) {
            $builtRouteMaps[] = $routeMapBuilder->build();
        }

        $routeCollection = new RouteCollection();
        $routeCollection->addMany($builtRouteMaps);

        return $routeCollection;
    }

    /**
     * Creates a group of routes that share similar options
     * 
     * @param RouteGroupOptions $groupOptions The list of options shared by all routes in the group
     * @param Closure $callback The callback that accepts an instance of this class
     */
    public function group(RouteGroupOptions $groupOptions, Closure $callback) : void
    {
        array_push($this->groupOptionsStack, $groupOptions);
        $callback($this);
        array_pop($this->groupOptionsStack);
    }

    /**
     * Creates a route builder with some values already set
     * 
     * @param array|string $httpMethods The HTTP method or list of methods the route uses
     * @param string $pathTemplate The path template
     * @param string|null $hostTemplate The host template
     * @param bool $isHttpsOnly Whether or not the route is HTTPS-only
     * @return RouteBuilder The configured route builder
     */
    public function map(
        $httpMethods,
        string $pathTemplate,
        string $hostTemplate = null,
        bool $isHttpsOnly = false
    ) : RouteBuilder {
        $this->applyGroupRouteTemplates($pathTemplate, $hostTemplate, $isHttpsOnly);
        $parsedRouteTemplate = $this->routeTemplateParser->parse($pathTemplate, $hostTemplate, $isHttpsOnly);
        $routeBuilder = new RouteBuilder($httpMethods, $parsedRouteTemplate);
        $this->applyGroupMiddleware($routeBuilder);

        return $routeBuilder;
    }

    /**
     * Applies a group's middleware to the input route builder
     * 
     * @param RouteBuilder $routeBuilder The route builder to bind middleware to
     */
    private function applyGroupMiddleware(RouteBuilder &$routeBuilder) : void
    {
        $groupMiddlewareBindings = [];

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupMiddlewareBindings = array_merge($groupMiddlewareBindings, $groupOptions->getMiddlewareBindings());
        }

        $routeBuilder->withManyMiddleware($groupMiddlewareBindings);
    }

    /**
     * Applies all the group options to a route
     * 
     * @param string $pathTemplate The path template to apply settings to
     * @param string|null $hostTemplate The host template to apply settings to
     * @param bool $isHttpsOnly Whether or not the group is HTTPS-only
     */
    private function applyGroupRouteTemplates(
        string &$pathTemplate,
        string &$hostTemplate = null,
        bool &$isHttpsOnly = false
    ) : void {
        $groupPathTemplate = '';
        $groupHostTemplate = '';
        $groupIsHttpsOnly = false;

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupPathTemplate .= $groupOptions->getPathTemplate();
            $groupHostTemplate = $groupOptions->getHostTemplate() . $groupHostTemplate;
            $groupIsHttpsOnly = $groupIsHttpsOnly || $groupOptions->isHttpsOnly();
        }

        $pathTemplate = $groupPathTemplate . $pathTemplate;
        $hostTemplate = $groupHostTemplate . ($hostTemplate ?? '');
        $isHttpsOnly = $isHttpsOnly || $groupIsHttpsOnly;
    }
}
