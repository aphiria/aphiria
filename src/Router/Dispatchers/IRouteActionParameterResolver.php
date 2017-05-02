<?php
namespace Opulence\Router\Dispatchers;

use ReflectionParameter;

/**
 * Defines the interface for route action parameter resolvers to implement
 */
interface IRouteActionParameterResolver
{
    /**
     * Resolves a parameter from a route action
     *
     * @param ReflectionParameter $parameter The parameter to resolve
     * @param array $routeVars The mapping of route var names => values
     * @param string $rawBody The raw request body
     * @param string $contentType The content type of the request
     * @param array $queryVars The mapping of query var names => values
     * @return mixed The resolved parameter
     */
    public function resolveParameter(
        ReflectionParameter $parameter,
        array $routeVars,
        string $rawBody,
        string $contentType,
        array $queryVars
    );
}
