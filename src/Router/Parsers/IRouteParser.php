<?php
namespace Opulence\Router\Parsers;

use Opulence\Router\ParsedRoute;
use Opulence\Router\Route;

/**
 * Defines the interface for route parsers to implement
 */
interface IRouteParser
{
    public function parse(Route $route) : ParsedRoute;
}