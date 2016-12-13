<?php
namespace Opulence\Net\Http\Requests;

/**
 * Defines the interface for HTTP request messages factories to implement
 */
interface IHttpRequestMessageFactory
{
    public function createFromGlobals(
        array $query = null,
        array $post = null,
        array $cookies = null,
        array $server = null,
        array $files = null,
        array $env = null,
        string $rawBody = null
    ) : IHttpRequestMessage;
    
    public function createFromUri(
        string $uri,
        string $method,
        array $parameters = [],
        array $cookies = [],
        array $server = [],
        array $files = [],
        array $env = [],
        string $rawBody = null
    ) : IHttpRequestMessage;
}