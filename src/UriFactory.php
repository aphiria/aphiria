<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net;

use InvalidArgumentException;

/**
 * Defines a URI factory
 */
class UriFactory
{
    /**
     * Creates a URI from a string
     *
     * @param string $uri The string URI to convert
     * @return Uri The created URI
     */
    public function createUriFromString(string $uri) : Uri
    {
        if (($parsedUri = parse_url($uri)) === false) {
            throw new InvalidArgumentException("URI $uri is malformed");
        }

        return new Uri(
            $parsedUri['scheme'] ?? null,
            $parsedUri['user'] ?? null,
            $parsedUri['pass'] ?? null,
            $parsedUri['host'] ?? null,
            $parsedUri['port'] ?? null,
            $parsedUri['path'] ?? null,
            $parsedUri['query'] ?? null,
            $parsedUri['fragment'] ?? null
        );
    }
}
