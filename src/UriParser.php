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
    /** @var array The mapping of raw query strings to their parsed collections */
    private $parsedQueryStringCache = [];

    /**
     * Parses a URI's query string into a collection
     *
     * @param Uri $uri The URI to parse
     * @return Collection The parsed query string
     */
    public function parseQueryString(Uri $uri) : Collection
    {
        $queryString = $uri->getQueryString();

        if (!isset($this->parsedQueryStringCache[$queryString])) {
            $parsedQueryString = [];
            parse_str($queryString, $parsedQueryString);
            $this->parsedQueryStringCache[$queryString] = new Collection($parsedQueryString);
        }

        return $this->parsedQueryStringCache[$queryString];
    }
}
