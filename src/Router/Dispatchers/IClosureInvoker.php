<?php
namespace Opulence\Router\Dispatchers;

use Closure;
use Opulence\Router\MatchedRoute;

/**
 * Defines the interface for closure invokers to implement
 */
interface IClosureInvoker
{
    /**
     * Invokes a closure
     * 
     * @param Closure $closure The closure to invoke
     * @param MatchedRoute $matchedRoute The matched route
     * @param string $rawBody The raw HTTP request body
     * @param string $contentType The content type of the request
     * @param array $queryVars The query vars
     * @return mixed The return value of the closure
     */
    public function invokeClosure(
        Closure $closure,
        MatchedRoute $matchedRoute,
        string $rawBody,
        string $contentType,
        array $queryVars
    );
}
