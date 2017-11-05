<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Collections\IImmutableDictionary;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Net\Http\HttpHeaderParser;
use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the request header parser
 */
class RequestHeaderParser extends HttpHeaderParser
{
    /**
     * Parses the request headers for all cookie values
     *
     * @param HttpHeaders $headers The headers to parse
     * @return IImmutableDictionary The mapping of cookie names to values
     */
    public function parseCookies(HttpHeaders $headers) : IImmutableDictionary
    {
        $cookieValues = [];

        if (!$headers->tryGetFirst('Cookie', $cookieValues)) {
            return new ImmutableHashTable([]);
        }

        return $this->parseParameters($cookieValues);
    }
}
