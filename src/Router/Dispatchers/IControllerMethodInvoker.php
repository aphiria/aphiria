<?php
namespace Opulence\Router\Dispatchers;

use Opulence\Router\MatchedRoute;

/**
 * Defines the interface for controller method invokers to implement
 */
interface IControllerMethodInvoker
{
    /**
     * Invokes a controller method
     * 
     * @param object $controller The instance of the controller to call
     * @param string $methodName The name of the method to invoke
     * @param MatchedRoute $matchedRoute The matched route
     * @param string $rawBody The raw HTTP request body
     * @param string $contentType The content type of the request
     * @param array $queryVars The query vars
     * @return mixed The return value of the controller method
     */
    public function invokeMethod(
        $controller,
        string $methodName,
        MatchedRoute $matchedRoute,
        string $rawBody,
        string $contentType,
        array $queryVars
    );
}
