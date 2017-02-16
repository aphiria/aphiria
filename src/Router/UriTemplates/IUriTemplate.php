<?php
namespace Opulence\Router\UriTemplates;

/**
 * Defines the interface for URI templates to implement
 */
interface IUriTemplate
{
    /**
     * Builds the URI template from a list of route vars
     *
     * @param array $routeVars The mapping of route var names to their values
     * @return string The built URI
     */
    public function buildTemplate(array $routeVars) : string;

    /**
     * Tries to match on a value and, if successful, sets the list of matching route vars
     *
     * @param string $uri The URI to try to match on
     * @param array $routeVars The list of route vars found during a successful match
     * @return bool True if the route template matched the input value, otherwise false
     */
    public function tryMatch(string $uri, array &$routeVars = []) : bool;
}
