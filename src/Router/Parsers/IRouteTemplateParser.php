<?php
namespace Opulence\Router\Parsers;

use Opulence\Router\IRouteTemplate;

/**
 * Defines the interface for route template parsers to implement
 */
interface IRouteTemplateParser
{
    public function parse(string $pathTemplate, string $hostTemplate = null) : IRouteTemplate;
}