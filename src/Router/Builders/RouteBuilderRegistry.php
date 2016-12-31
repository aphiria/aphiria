<?php
namespace Opulence\Router\Builders;

use Closure;
use Opulence\Router\Dispatchers\IRouteActionFactory;
use Opulence\Router\Parsers\IRouteTemplateParser;

/**
 * Defines the route builder registry
 */
class RouteBuilderRegistry
{
    /** @var RuteBuilder[] The list of registered route builders */
    private $routeBuilders = [];
    /** @var IRouteTemplateParser The route parser */
    private $routeTemplateParser = null;
    /** @var IRouteActionFactory The route action factory */
    private $routeActionFactory = null;
    /** @var RouteGroupOptions The stack of route group options */
    private $groupOptionsStack = [];
    
    public function __construct(IRouteTemplateParser $routeTemplateParser, IRouteActionFactory $routeActionFactory)
    {
        $this->routeTemplateParser = $routeTemplateParser;
        $this->routeActionFactory = $routeActionFactory;
    }
    
    public function buildAll() : array
    {
        $builtRouteMaps = [];
        
        foreach ($this->routeBuilders as $routeMapBuilder) {
            $builtRouteMaps[] = $routeMapBuilder->build();
        }
        
        return $builtRouteMaps;
    }
    
    public function group(RouteGroupOptions $groupOptions, Closure $callback)
    {
        array_push($this->groupOptionsStack, $groupOptions);
        $callback($this);
        array_pop($this->groupOptionsStack);
    }
    
    public function map($methods, string $pathTemplate, string $hostTemplate = null, bool $isHttpsOnly = false) : RouteBuilder
    {
        $this->applyGroupOptionsToRoute($pathTemplate, $hostTemplate);
        $parsedPathTemplate = $this->routeTemplateParser->parse($pathTemplate);
        $parsedHostTemplate = $hostTemplate == null ? $hostTemplate : $this->routeTemplateParser->parse($hostTemplate);
        $routeBuilder = new RouteBuilder($this->routeActionFactory, (array)$methods, $parsedPathTemplate, $parsedHostTemplate, $isHttpsOnly);
        $this->applyGroupOptionsToRouteBuilder($routeBuilder);
        
        return $routeBuilder;
    }
    
    private function applyGroupOptionsToRoute(string &$pathTemplate, string &$hostTemplate = null)
    {
        $groupPathTemplate = "";
        $groupHostTemplate = "";
        $groupIsHttpsOnly = false;

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupPathTemplate .= $groupOptions->getPathTemplate();
            $groupHostTemplate = $groupOptions->getHostTemplate() . $groupHostTemplate;
            $groupIsHttpsOnly = $groupIsHttpsOnly || $groupOptions->isHttpsOnly();
        }

        $pathTemplate = $groupPathTemplate . $pathTemplate;
        $hostTemplate = $groupHostTemplate . ($hostTemplate ?? "");
    }
    
    private function applyGroupOptionsToRouteBuilder(RouteBuilder &$routeBuilder)
    {
        $groupMiddleware = [];
        
        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupMiddleware = array_merge($groupMiddleware, $groupOptions->getMiddleware());
        }
        
        $routeBuilder->withMiddleware($groupMiddleware);
    }
}