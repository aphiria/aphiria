<?php
namespace Opulence\Router\Parsers;

use Opulence\Router\RouteTemplate;

/**
 * Defines the interface for route template parsers to implement
 */
interface IRouteTemplateParser
{
    public function parse(string $routeTemplate) : RouteTemplate;
}