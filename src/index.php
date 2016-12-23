<?php
use Opulence\Router\Route;
use Opulence\Router\RouteMapBuilder;
use Opulence\Router\RouteMapBuilderRegistry;
use Opulence\Router\Router;

$routeMapBuilderRegistry = new RouteMapBuilderRegistry(new RouteParser());
$routeMapBuilderRegistry->map(new Route("GET", "users/:userId"))
        ->toController("UserController", "showProfile")
        ->withName("UserProfile");

$router = new Router($routeMapBuilderRegistry->buildAll());