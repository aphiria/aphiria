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
     * @param array|null $query The query string variables, or null if using $_GET
     * @param array|null $post The post variables, or null if using $_POST
     * @param array|null $cookies The cookies, or null if using $_COOKIE
     * @param array|null $server The server variables, or null if using $_SERVER
     * @param array|null $files The files, or null if using $_FILES
     * @param array|null $env The environment variables, or null if using $_ENV
     * @param string|null $rawBody The raw request message body, or null if using the input stream
     * @return IHttpRequestMessage The created request message
     * @throws InvalidArgumentException Thrown if any of the expected global values are not set correctly
     */
    public function createFromGlobals(
        array $query = null,
        array $post = null,
        array $cookies = null,
        array $server = null,
        array $files = null,
        array $env = null,
        string $rawBody = null
    ) : IHttpRequestMessage;

    /**
     * Creates a request message from a URI
     *
     * @param string $uri The URI
     * @param string $method The HTTP method
     * @param array $parameters The parameters (will be bound to query if GET request, otherwise bound to post)
     * @param array $cookies The cookie names to values
     * @param array $server The server variable names to values
     * @param array $files The file data
     * @param array $env The mapping of environment variable names to values
     * @param string|null $rawBody The raw body
     * @return IHttpRequestMessage The created request message
     */
    public function createFromUri(
        string $uri,
        string $method,
        array $parameters = [],
        array $cookies = [],
        array $server = [],
        array $files = [],
        array $env = [],
        ?string $rawBody = null
    ) : IHttpRequestMessage;
}
