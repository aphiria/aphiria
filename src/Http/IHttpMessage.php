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
     * @return IHttpBody The body
     */
    public function getBody() : IHttpBody;

    /**
     * Gets the headers of the HTTP message
     *
     * @return IHttpHeaders The headers
     */
    public function getHeaders() : IHttpHeaders;

    /**
     * Sets the body of the HTTP message
     *
     * @param IHttpBody $body The body
     */
    public function setBody(IHttpBody $body) : void;
}
