<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\IHttpMessage;
use Opulence\Net\IUri;

/**
 * Defines the interface for HTTP request messages to implement
 */
interface IHttpRequestMessage extends IHttpMessage
{
    /**
     * Gets the HTTP method for the request
     *
     * @return string The HTTP method
     */
    public function getMethod() : string;

    /**
     * Gets the properties of the request
     *
     * @return array The mapping of property names to values
     */
    public function getProperties() : array;

    /**
     * Gets the URI of the request
     *
     * @return IUri The URI
     */
    public function getRequestUri() : IUri;

    /**
     * Sets the HTTP method of the request
     *
     * @param string $method The HTTP method
     */
    public function setMethod(string $method) : void;

    /**
     * Sets the request URI
     *
     * @param IUri $requestUri The request URI
     */
    public function setRequestUri(IUri $requestUri) : void;
}
