<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use InvalidArgumentException;
use Opulence\Collections\HashTable;
use Opulence\Collections\IDictionary;
use Opulence\IO\Streams\Stream;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Uri;

/**
 * Defines the factory that creates requests
 */
class RequestFactory
{
    /** @const The name of the request property that stores the client IP address */
    private const CLIENT_IP_ADDRESS_PROPERTY = 'CLIENT_IP_ADDRESS';
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
    /** @var array The default mapping of header names to trusted proxy header names */
    private static $defaultTrustedHeaderNames = [
        'HTTP_FORWARDED' => 'HTTP_FORWARDED',
        'HTTP_CLIENT_IP' => 'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_HOST' => 'HTTP_X_FORWARDED_HOST',
        'HTTP_CLIENT_PORT' => 'HTTP_X_FORWARDED_PORT',
        'HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO'
    ];
    /** @var array The list of header names whose values should be URL-decoded */
    private static $headersToUrlDecode = ['HTTP_COOKIE' => true];
    /** @var array The list of trusted proxy IP addresses */
    protected $trustedProxyIPAddresses = [];
    /** @var array The mapping of header names to trusted header names*/
    protected $trustedHeaderNames = [];

    /**
     * @param array $trustedProxyIPAddresses The list of trusted proxy IP addresses
     * @param array $trustedHeaderNames The mapping of additional header names to trusted header names
     */
    public function __construct(array $trustedProxyIPAddresses = [], array $trustedHeaderNames = [])
    {
        $this->trustedProxyIPAddresses = $trustedProxyIPAddresses;
        $this->trustedHeaderNames = array_merge(self::$defaultTrustedHeaderNames, $trustedHeaderNames);
    }

    /**
     * Creates a request message from PHP globals
     *
     * @param array $server The server super global
     * @return IHttpRequestMessage The created request message
     */
    public function createRequestFromGlobals(array $server) : IHttpRequestMessage
    {
        $method = $server['REQUEST_METHOD'] ?? 'GET';

        // Permit the overriding of the request method for POST requests
        if ($method === 'POST' && isset($server['X-HTTP-METHOD-OVERRIDE'])) {
            $method = $server['X-HTTP-METHOD-OVERRIDE'];
        }

        $uri = $this->createUriFromGlobals($server);
        $headers = $this->createHeadersFromGlobals($server);
        $body = new StreamBody(new Stream(fopen('php://input', 'r')));
        $properties = $this->createProperties($server);

        return new Request($method, $uri, $headers, $body, $properties);
    }

    /**
     * Creates headers from PHP globals
     *
     * @param array $server The global server array
     * @return HttpHeaders The request headers
     */
    protected function createHeadersFromGlobals(array $server) : HttpHeaders
    {
        $headers = new HttpHeaders();

        foreach ($server as $name => $value) {
            $decodedValue = isset(self::$headersToUrlDecode[$name]) ? urldecode($value) : $value;

            if (isset(self::$specialCaseHeaders[$name])) {
                $headers->add($name, $decodedValue);
            } elseif (strpos($name, 'HTTP_') === 0) {
                // Drop the "HTTP_"
                $normalizedName = substr($name, 5);
                $headers->add($normalizedName, $decodedValue);
            }
        }

        return $headers;
    }

    /**
     * Creates properties
     *
     * @param array $server The global server array
     * @return IDictionary The list of properties
     */
    protected function createProperties(array $server) : IDictionary
    {
        $properties = new HashTable();

        // Set the client IP address as a property
        if (($clientIPAddress = $this->getClientIPAddress($server)) !== null) {
            $properties->add(self::CLIENT_IP_ADDRESS_PROPERTY, $clientIPAddress);
        }

        return $properties;
    }

    /**
     * Creates a URI from PHP globals
     *
     * @param array $server The global server array
     * @return Uri The URI
     * @throws InvalidArgumentException Thrown if the host is malformed
     */
    protected function createUriFromGlobals(array $server) : Uri
    {
        if ($this->isUsingTrustedProxy($server) && isset($server[$this->trustedHeaderNames['HTTP_CLIENT_PROTO']])) {
            $protoString = $server[$this->trustedHeaderNames['HTTP_CLIENT_PROTO']];
            $protoArray = explode(',', $protoString);
            $isSecure = count($protoArray) > 0 && in_array(strtolower($protoArray[0]), ['https', 'ssl', 'on']);
        } else {
            $isSecure = isset($server['HTTPS']) && $server['HTTPS'] !== 'off';
        }

        $rawProtocol = isset($server['SERVER_PROTOCOL']) ? strtolower($server['SERVER_PROTOCOL']) : 'http/1.1';
        $scheme = substr($rawProtocol, 0, strpos($rawProtocol, '/')) . ($isSecure ? 's' : '');
        $user = $server['PHP_AUTH_USER'] ?? null;
        $password = $server['PHP_AUTH_PW'] ?? null;
        $port = null;

        if ($this->isUsingTrustedProxy($server)) {
            if (isset($server[$this->trustedHeaderNames['HTTP_CLIENT_PORT']])) {
                $port = (int)$server[$this->trustedHeaderNames['HTTP_CLIENT_PORT']];
            } elseif (
                isset($server[$this->trustedHeaderNames['HTTP_CLIENT_PROTO']]) &&
                $server[$this->trustedHeaderNames['HTTP_CLIENT_PROTO']] === 'https'
            ) {
                $port = 443;
            }
        }

        if ($port === null) {
            $port = isset($server['SERVER_PORT']) ? (int)$server['SERVER_PORT'] : null;
        }

        if (
            $this->isUsingTrustedProxy($server) &&
            isset($server[$this->trustedHeaderNames['HTTP_CLIENT_HOST']])
        ) {
            $hostWithPort = explode(',', $server[$this->trustedHeaderNames['HTTP_CLIENT_HOST']]);
            $hostWithPort = trim(end($hostWithPort));
        } else {
            $hostWithPort = $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? $server['SERVER_ADDR'] ?? '';
        }

        // Remove the port from the host
        $host = strtolower(preg_replace("/:\d+$/", '', trim($hostWithPort)));

        // Check for forbidden characters
        if (!empty($host) && !empty(preg_replace("/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/", '', $host))) {
            throw new InvalidArgumentException("Invalid host \"$host\"");
        }

        $path = parse_url('http://foo.com' . ($server['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        $path = $path === false ? '' : ($path ?? '');
        $queryString = $server['QUERY_STRING'] ?? '';

        if ($queryString === '') {
            $queryString = parse_url('http://foo.com' . ($server['REQUEST_URI'] ?? ''), PHP_URL_QUERY);
            $queryString = $queryString === false || $queryString === '' ? null : $queryString;
        }

        // The "?" is simply the separator for the query string, not actually part of the query string
        $queryString = ltrim($queryString, '?');
        $uriString = "$scheme://";

        if ($user !== null) {
            $uriString .= "$user:" . ($password ?? '') .'@';
        }

        $uriString .= "{$host}" . ($port === null ? '' : ":$port") . "{$path}?{$queryString}";

        return new Uri($uriString);
    }

    /**
     * Gets the client IP address
     *
     * @param array $server The global server array
     * @return string|null The client IP address if one was found, otherwise null
     */
    protected function getClientIPAddress(array $server) : ?string
    {
        $serverRemoteAddress = $server['REMOTE_ADDR'] ?? null;

        if ($this->isUsingTrustedProxy($server)) {
            return $serverRemoteAddress ?? null;
        }

        $ipAddresses = [];

        // RFC 7239
        if (isset($server[$this->trustedHeaderNames['HTTP_FORWARDED']])) {
            $header = $server[$this->trustedHeaderNames['HTTP_FORWARDED']];
            preg_match_all("/for=(?:\"?\[?)([a-z0-9:\.\-\/_]*)/", $header, $matches);
            $ipAddresses = $matches[1];
        } elseif (isset($server[$this->trustedHeaderNames['HTTP_CLIENT_IP']])) {
            $ipAddresses = explode(',', $server[$this->trustedHeaderNames['HTTP_CLIENT_IP']]);
            $ipAddresses = array_map('trim', $ipAddresses);
        }

        if ($serverRemoteAddress !== null) {
            $ipAddresses[] = $serverRemoteAddress;
        }

        $fallbackIPAddresses = count($ipAddresses) === 0 ? [] : [$ipAddresses[0]];

        foreach ($ipAddresses as $index => $ipAddress) {
            // Check for valid IP address
            if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                unset($ipAddresses[$index]);
            }

            // Don't accept trusted proxies
            if (in_array($ipAddress, $this->trustedProxyIPAddresses)) {
                unset($ipAddresses[$index]);
            }
        }

        $clientIPAddresses = count($ipAddresses) === 0 ? $fallbackIPAddresses : array_reverse($ipAddresses);

        return $clientIPAddresses[0] ?? null;
    }

    /**
     * Gets whether or not we're using a trusted proxy
     *
     * @param array $server The global server array
     * @return bool True if using a trusted proxy, otherwise false
     */
    protected function isUsingTrustedProxy(array $server) : bool
    {
        return in_array($server['REMOTE_ADDR'] ?? '', $this->trustedProxyIPAddresses);
    }
}
