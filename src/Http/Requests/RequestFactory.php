<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\Headers;
use Opulence\Net\Http\IHttpHeaders;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Http\StringBody;

/**
 * Defines the factory that creates requests
 */
class RequestFactory implements IHttpRequestMessageFactory
{
    /** @var array The list of HTTP request headers that don't begin with "HTTP_" */
    private static $specialCaseHeaders = [
        'AUTH_TYPE' => true,
        'CONTENT_LENGTH' => true,
        'CONTENT_TYPE' => true,
        'PHP_AUTH_DIGEST' => true,
        'PHP_AUTH_PW' => true,
        'PHP_AUTH_TYPE' => true,
        'PHP_AUTH_USER' => true
    ];
    
    /**
     * @inheritdoc
     */
    public function createFromGlobals(
        array $query = null,
        array $post = null,
        array $cookies = null,
        array $server = null,
        array $files = null,
        array $env = null,
        string $rawBody = null
    ) : IHttpRequestMessage {
        $method = $server['REQUEST_METHOD'] ?? null;
        
        // Permit the overriding of the request method for POST requests
        if ($method === 'POST' && isset($server['X-HTTP-METHOD-OVERRIDE'])) {
            $method = $server['X-HTTP-METHOD-OVERRIDE'];
        }
        
        $headers = $this->createHeadersFromGlobals($server);
        $body = $this->createBodyFromRawBody($rawBody);

        // Todo: Keep going
        // Todo: Where do the request "properties" come from?
    }

    /**
     * @inheritdoc
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
    ) : IHttpRequestMessage {
        // Todo
    }
    
    /**
     * Creates a body from the raw body
     * 
     * @param string $rawBody The raw body if one is specified, otherwise we use the input stream
     */
    private function createBodyFromRawBody(string $rawBody = null) : IHttpBody
    {
        if ($rawBody === null) {
            return new StreamBody(new Stream(fopen('php://input', 'r+')));
        }
        
        return new StringBody($rawBody);
    }
    
    /**
     * Creates headers from globals
     * 
     * @param array $server The global server array
     * @return IHttpHeaders The request headers
     */
    private function createHeadersFromGlobals(array $server) : IHttpHeaders
    {
        $headers = new Headers();
        
        foreach ($server as $name => $value) {
            if (isset(self::$specialCaseHeaders[$name])) {
                $headers->set($name, $value);
            } elseif (strpos($value, 'HTTP_') === 0) {
                // Drop the "HTTP_", capitalize it, and replace "_" with "-"
                $normalizedName = strtr(strtoupper(substr($name, 5)), '_', '-');
                $headers->set($normalizedName, $value);
            }
        }
        
        return $headers;
    }
}
