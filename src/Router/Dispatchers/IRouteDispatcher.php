<?php
namespace Opulence\Router\Dispatchers;

use Opulence\Router\MatchedRoute;

/**
 * Defines the interface for route dispatchers to implement
 */
interface IRouteDispatcher
{
    /**
     * Dispatches a matched route
     * 
     * @param MatchedRoute $matchedRoute The matched route
     * @param string $rawRequestBody The raw request body
     * @param string $contentType The request content type
     * @param array $queryVars The query vars
     * @return mixed The return type of the matched route's action
     */
    public function dispatch(
        MatchedRoute $matchedRoute,
        string $rawRequestBody,
        string $contentType,
        array $queryVars
    );
}
