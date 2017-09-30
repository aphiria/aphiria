<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net;

/**
 * Defines the URI parser
 */
class UriParser
{
    /** @var array The mapping of raw query strings to their parsed values */
    private $parsedQueryStringCache = [];

    /**
     * Gets a query string parameter from a URI
     *
     * @param Uri $uri The URI to read from
     * @param string $name The name of the query string parameter to get
     * @return string|array|null The value of the query string parameter if found, otherwise null
     */
    public function getQueryStringParam(Uri $uri, string $name)
    {
        if (!$this->hasQueryStringParam($uri, $name)) {
            return null;
        }

        return $this->getParsedQueryString($uri->getQueryString())[$name];
    }

    /**
     * Checks if a query string parameter exists in a URI
     *
     * @param Uri $uri The URI to read from
     * @param string $name The name of the query string parameter to check
     * @return bool True if the query string parameter exists, otherwise false
     */
    public function hasQueryStringParam(Uri $uri, string $name) : bool
    {
        return array_key_exists($name, $this->getParsedQueryString($uri->getQueryString()));
    }

    /**
     * Either parses and caches a query string and returns it, or returns the cached parsed query string
     *
     * @param string $queryString The raw query string to parse
     * @return array The mapping of parsed query string parameters to values
     */
    private function getParsedQueryString(string $queryString) : array
    {
        if (!isset($this->parsedQueryStringCache[$queryString])) {
            $parsedQueryString = [];
            parse_str($queryString, $parsedQueryString);
            $this->parsedQueryStringCache[$queryString] = $parsedQueryString;
        }

        return $this->parsedQueryStringCache[$queryString];
    }
}
