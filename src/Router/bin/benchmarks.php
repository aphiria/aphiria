<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Aphiria\Routing\Matchers\TrieRouteMatcher;
use Aphiria\Routing\RouteCollection as AphiriaRouteCollection;
use Aphiria\Routing\RouteCollectionBuilder;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieFactory;
use FastRoute\RouteCollector;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * This benchmark is testing how fast a realistic route may take to match.
 * Specifically, we're adding 400 routes with the structure "/abc123/123/:foo/123".
 * We're testing a mix of literal and variable path segments, and averaging
 * the amount of time it takes to match each of 400 unique routes registered.
 * The goal is to negate any performance gains and losses by testing routes
 * registered first or last, and instead focus on matching each route.
 *
 * We run these benchmarks with an optimized Composer autoload, and with opcache
 * enabled.
 */

$numTests = 100;
$numRoutes = 400;

/**
 * Formats the results of a benchmark
 *
 * @param string $library The library being benchmarked
 * @param float $memoryTakenBytes The amount of memory taken in bytes
 * @param float $timeTakenSeconds The amount of time taken in seconds
 * @return string The formatted results
 */
function formatResults(string $library, float $memoryTakenBytes, float $timeTakenSeconds): string
{
    global $numTests, $numRoutes;

    return \sprintf(
        "%s: \n    Total Time: %sms\n    Avg Match: %sms\n    Matches/s: %s/s\n    Memory: %sMB\n",
        $library,
        \number_format(1000 * $timeTakenSeconds),
        \round(1000 * $timeTakenSeconds / ($numTests * $numRoutes), 4),
        \number_format(\round(($numTests * $numRoutes) / $timeTakenSeconds)),
        \round($memoryTakenBytes / 1024 / 1024, 3)
    );
}

echo 'Attempting to match /abc$i/$i/:foo/$i for $i 0-' . ($numRoutes - 1) . "\n";
echo "--------------------------------------------------\n";

/**
 * Symfony benchmark
 */

$startMemory = \memory_get_usage();
$routes = new SymfonyRouteCollection();

for ($routeIter = 0;$routeIter < $numRoutes;$routeIter++) {
    $routes->add("f$routeIter", new SymfonyRoute("/abc$routeIter/$routeIter/{foo}/$routeIter", methods: 'GET'));
}

$dumper = new CompiledUrlMatcherDumper($routes);
$router = new CompiledUrlMatcher($dumper->getCompiledRoutes(), new RequestContext(method: 'GET'));
$startTime = \microtime(true);

for ($testIter = 0;$testIter < $numTests;$testIter++) {
    for ($routeIter = 0;$routeIter < $numRoutes;$routeIter++) {
        $router->match("/abc$routeIter/$routeIter/def/$routeIter");
    }
}

echo \formatResults('Symfony', \memory_get_usage() - $startMemory, \microtime(true) - $startTime);

/**
 * Aphiria benchmark
 */

$startMemory = \memory_get_usage();
$routesBuilder = new RouteCollectionBuilder();

for ($routeIter = 0;$routeIter < $numRoutes;$routeIter++) {
    $routesBuilder->route('GET', "/abc$routeIter/$routeIter/:foo/$routeIter")
        ->mapsToMethod('Foo', (string)$routeIter);
}

$routes = new AphiriaRouteCollection();
$routes->addMany($routesBuilder->build()->values);
$routeMatcher = new TrieRouteMatcher((new TrieFactory($routes))->createTrie());
$startTime = \microtime(true);

for ($testIter = 0;$testIter < $numTests;$testIter++) {
    for ($routeIter = 0;$routeIter < $numRoutes;$routeIter++) {
        $routeMatcher->matchRoute('GET', '', "/abc$routeIter/$routeIter/def/$routeIter");
    }
}

echo \formatResults('Aphiria', \memory_get_usage() - $startMemory, \microtime(true) - $startTime);

/**
 * FastRoute benchmark
 */

$startMemory = \memory_get_usage();
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $routes) use ($numRoutes) {
    for ($routeIter = 0; $routeIter < $numRoutes; ++$routeIter) {
        $routes->addRoute('GET', "/abc$routeIter/$routeIter/{foo}/$routeIter", "f$routeIter");
    }
});
$startTime = \microtime(true);

for ($testIter = 0;$testIter < $numTests;$testIter++) {
    for ($routeIter = 0;$routeIter < $numRoutes;$routeIter++) {
        $dispatcher->dispatch('GET', "/abc$routeIter/$routeIter/def/$routeIter");
    }
}

echo \formatResults('FastRoute', \memory_get_usage() - $startMemory, \microtime(true) - $startTime);
