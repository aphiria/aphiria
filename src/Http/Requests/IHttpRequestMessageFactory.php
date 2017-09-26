<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use InvalidArgumentException;

/**
 * Defines the interface for HTTP request messages factories to implement
 */
interface IHttpRequestMessageFactory
{
    // Todo: Does this interface really even need to exist?  What's a use-case?
    /**
     * Creates a request message from PHP globals
     *
     * @param array|null $cookies The cookies, or null if using $_COOKIE
     * @param array|null $server The server variables, or null if using $_SERVER
     * @param array|null $files The files, or null if using $_FILES
     * @param string|null $rawBody The raw request message body, or null if using the input stream
     * @return IHttpRequestMessage The created request message
     * @throws InvalidArgumentException Thrown if any of the expected global values are not set correctly
     */
    public function createFromGlobals(
        array $cookies = null,
        array $server = null,
        array $files = null,
        string $rawBody = null
    ) : IHttpRequestMessage;
}
