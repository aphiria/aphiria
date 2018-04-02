<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Formatting;

use Opulence\Collections\IImmutableDictionary;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Uri;

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
     * @return IImmutableDictionary The parsed query string
     */
    public function parseQueryString(Uri $uri): IImmutableDictionary
    {
        $queryString = $uri->getQueryString();

        if (!isset($this->parsedQueryStringCache[$queryString])) {
            $parsedQueryString = [];
            parse_str($queryString, $parsedQueryString);
            $kvps = [];

            foreach ($parsedQueryString as $key => $value) {
                $kvps[] = new KeyValuePair($key, $value);
            }

            $this->parsedQueryStringCache[$queryString] = new ImmutableHashTable($kvps);
        }

        return $this->parsedQueryStringCache[$queryString];
    }
}
