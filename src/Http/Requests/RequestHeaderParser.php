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
use Opulence\Net\Http\HttpHeaderParser;
use Opulence\Net\Http\HttpHeaders;
use OutOfBoundsException;

/**
 * Defines the request header parser
 */
class RequestHeaderParser extends HttpHeaderParser
{
    /**
     * Gets all the cookie values from the request headers
     *
     * @return IImmutableDictionary The mapping of cookie names to values
     * @throws OutOfBoundsException Thrown if no cookies were found
     */
    public function getAllCookies(HttpHeaders $headers) : IImmutableDictionary
    {
        return $this->parseParameters($headers->getFirst('Cookie'));
    }


    /**
     * Gets a cookie value from the request headers
     *
     * @param HttpHeaders $headers The request headers to parse
     * @param string $name The name of the cookie whose value we want
     * @return mixed The value of the cookie
     * @throws OutOfBoundsException Thrown if the cookie does not exist
     */
    public function getCookie(HttpHeaders $headers, string $name)
    {
        return $this->parseParameters($headers->getFirst('Cookie'))->get($name);
    }
}
