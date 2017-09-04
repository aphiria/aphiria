<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests\Parsers;

use Opulence\Net\Http\Requests\IHttpRequestMessage;

/**
 * Defines the interface for HTTP request property parsers to implement
 */
interface IHttpRequestPropertyParser
{
    /**
     * Parses a request to see if it's HTTPS
     *
     * @param IHttpRequestMessage $message The message to parse
     * @return bool True if the request is secure, otherwise false
     */
    public function isSecure(IHttpRequestMessage $message) : bool;
}
