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
use Opulence\Net\Uri;

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
        ?string $rawBody = null
    ) : IHttpRequestMessage {
        $method = $server['REQUEST_METHOD'] ?? null;

        // Permit the overriding of the request method for POST requests
        if ($method === 'POST' && isset($server['X-HTTP-METHOD-OVERRIDE'])) {
            $method = $server['X-HTTP-METHOD-OVERRIDE'];
        }

        $headers = $this->createHeadersFromGlobals($server);
        $body = $this->createBodyFromRawBody($rawBody);
        $uri = $this->createUriFromGlobals($server);

        // Todo: Create list of UploadedFiles
        return new Request($method, $headers, $body, $uri, ['Todo']);
    }

    /**
     * @inheritdoc
     */
    public function createFromUri(
        string $rawUri,
        string $method,
        array $parameters = [],
        array $cookies = [],
        array $server = [],
        array $files = [],
        ?string $rawBody = null
    ) : IHttpRequestMessage {
        $uri = Uri::createFromString($rawUri);
        $headers = $this->createHeadersFromGlobals($server);
        $body = $this->createBodyFromRawBody($rawBody);

        // Todo: Create list of UploadedFiles
        return new Request($method, $headers, $body, $uri, ['Todo']);
    }

    /**
     * Creates a body from the raw body
     *
     * @param string|null $rawBody The raw body if one is specified, otherwise we use the input stream
     */
    private function createBodyFromRawBody(?string $rawBody) : IHttpBody
    {
        if ($rawBody === null) {
            return new StreamBody(new Stream(fopen('php://input', 'r+')));
        }

        return new StringBody($rawBody);
    }

    /**
     * Creates headers from PHP globals
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
                // Drop the "HTTP_"
                $normalizedName = substr($name, 5);
                $headers->set($normalizedName, $value);
            }
        }

        return $headers;
    }

    /**
     * Creates a URI from PHP globals
     *
     * @param array $server The global server array
     * @return Uri The URI
     * @throws InvalidArgumentException Thrown if the host is malformed
     */
    private function createUriFromGlobals(array $server) : Uri
    {
        // Todo: Need to handle trusted proxies for determining protocol, port, and host
        $isSecure = isset($server['HTTPS']) && $server['HTTPS'] !== 'off';
        $rawProtocol = strtolower($server['SERVER_PROTOCOL']);
        $scheme = substr($rawProtocol, 0, strpos($rawProtocol, '/')) . ($isSecure ? 's' : '');
        $user = $server['PHP_AUTH_USER'] ?? null;
        $password = $server['PHP_AUTH_PW'] ?? null;
        $port = (int)$server['SERVER_PORT'];
        $hostWithPort = $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? $server['SERVER_ADDR'] ?? '';

        // Remove the port from the host
        $host = strtolower(preg_replace("/:\d+$/", '', trim($hostWithPort)));

        // Check for forbidden characters
        if (!empty($host) && !empty(preg_replace("/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/", '', $host))) {
            throw new InvalidArgumentException("Invalid host \"$host\"");
        }

        $path = parse_url('http://foo.com' . $server['REQUEST_URI'], PHP_URL_PATH);
        $path = $path === false ? '' : ltrim($path, '/');
        $queryString = $server['QUERY_STRING'];

        if ($queryString === '') {
            $queryString = parse_url('http://foo.com' . $server['REQUEST_URI'], PHP_URL_QUERY);
            $queryString = $queryString === false || $queryString === '' ? null : $queryString;
        }

        return new Uri($scheme, $user, $password, $host, $port, $path, $queryString, null);
    }
}
