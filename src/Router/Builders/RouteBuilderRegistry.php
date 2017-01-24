<?php
namespace Opulence\Router\Builders;

use Closure;
use Opulence\Router\Dispatchers\IRouteActionFactory;
use Opulence\Router\Parsers\IRouteTemplateParser;
use Opulence\Router\Parsers\RouteTemplateParser;
use Opulence\Router\Routes\RouteCollection;

/**
 * Defines the route builder registry
 */
class RouteBuilderRegistry
{
    /** @var RouteBuilder[] The list of registered route builders */
    private $routeBuilders = [];
    /** @var IRouteTemplateParser The route parser */
    private $routeTemplateParser = null;
    /** @var IRouteActionFactory The route action factory */
    private $routeActionFactory = null;
    /** @var RouteGroupOptions[] The stack of route group options */
    private $groupOptionsStack = [];

    public function __construct(
        IRouteActionFactory $routeActionFactory,
        IRouteTemplateParser $routeTemplateParser = null
    ) {
        $this->routeActionFactory = $routeActionFactory;
        $this->routeTemplateParser = $routeTemplateParser ?? new RouteTemplateParser();
    }

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

    public function group(RouteGroupOptions $groupOptions, Closure $callback) : void
    {
        array_push($this->groupOptionsStack, $groupOptions);
        $callback($this);
        array_pop($this->groupOptionsStack);
    }

    public function map(
        $methods,
        string $pathTemplate,
        string $hostTemplate = null,
        bool $isHttpsOnly = false
    ) : RouteBuilder {
        $this->applyGroupOptionsToRoute($pathTemplate, $hostTemplate);
        $parsedRouteTemplate = $this->routeTemplateParser->parse($pathTemplate, $hostTemplate);
        $routeBuilder = new RouteBuilder($this->routeActionFactory, $methods, $parsedRouteTemplate, $isHttpsOnly);
        $this->applyGroupOptionsToRouteBuilder($routeBuilder);

        return $routeBuilder;
    }

    private function applyGroupOptionsToRoute(string &$pathTemplate, string &$hostTemplate = null) : void
    {
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
    }

    private function applyGroupOptionsToRouteBuilder(RouteBuilder &$routeBuilder) : void
    {
        $groupMiddleware = [];

        foreach ($this->groupOptionsStack as $groupOptions) {
            $groupMiddleware = array_merge($groupMiddleware, $groupOptions->getMiddleware());
        }

        $routeBuilder->withMiddleware($groupMiddleware);
    }
}
