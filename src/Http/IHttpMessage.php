<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

/**
 * Defines the interface for all HTTP messages
 */
interface IHttpMessage
{
    /**
     * Gets the body of the HTTP message
     *
     * @return IHttpBody|null The body if there is one, otherwise null
     */
    public function getBody() : ?IHttpBody;

    /**
     * Gets the headers of the HTTP message
     *
     * @return HttpHeaders The headers
     */
    public function getHeaders() : HttpHeaders;

    /**
     * Gets the protocol version (eg '1.1' or '2.0') from the HTTP message
     *
     * @return string The protocol version
     */
    public function getProtocolVersion() : string;

    /**
     * Sets the body of the HTTP message
     *
     * @param IHttpBody $body The body
     */
    public function setBody(IHttpBody $body) : void;
}
