<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\Collections\HashTable;
use Aphiria\Collections\IDictionary;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the factory that creates requests
 */
class RequestFactory
{
    /** @const The name of the request property that stores the client IP address */
    private const CLIENT_IP_ADDRESS_PROPERTY_NAME = 'CLIENT_IP_ADDRESS';
    /** @var array<string, true> The list of HTTP request headers that don't begin with "HTTP_" */
    private static array $specialCaseHeaders = [
        'AUTH_TYPE' => true,
        'CONTENT_LENGTH' => true,
        'CONTENT_TYPE' => true,
        'PHP_AUTH_DIGEST' => true,
        'PHP_AUTH_PW' => true,
        'PHP_AUTH_TYPE' => true,
        'PHP_AUTH_USER' => true
    ];
    /** @var array<string, string> The default mapping of header names to trusted proxy header names */
    private static array $defaultTrustedHeaderNames = [
        'HTTP_FORWARDED' => 'HTTP_FORWARDED',
        'HTTP_CLIENT_IP' => 'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_HOST' => 'HTTP_X_FORWARDED_HOST',
        'HTTP_CLIENT_PORT' => 'HTTP_X_FORWARDED_PORT',
        'HTTP_CLIENT_PROTO' => 'HTTP_X_FORWARDED_PROTO'
    ];
    /** @var array<string, true> The list of HTTP request headers that permit multiple values */
    private static array $headersThatPermitMultipleValues = [
        'HTTP_ACCEPT' => true,
        'HTTP_ACCEPT_CHARSET' => true,
        'HTTP_ACCEPT_ENCODING' => true,
        'HTTP_ACCEPT_LANGUAGE' => true,
        'HTTP_ACCEPT_PATCH' => true,
        'HTTP_ACCEPT_RANGES' => true,
        'HTTP_ALLOW' => true,
        'HTTP_CACHE_CONTROL' => true,
        'HTTP_CONNECTION' => true,
        'HTTP_CONTENT_ENCODING' => true,
        'HTTP_CONTENT_LANGUAGE' => true,
        'HTTP_EXPECT' => true,
        'HTTP_IF_MATCH' => true,
        'HTTP_IF_NONE_MATCH' => true,
        'HTTP_PRAGMA' => true,
        'HTTP_PROXY_AUTHENTICATE' => true,
        'HTTP_TE' => true,
        'HTTP_TRAILER' => true,
        'HTTP_TRANSFER_ENCODING' => true,
        'HTTP_UPGRADE' => true,
        'HTTP_VARY' => true,
        'HTTP_VIA' => true,
        'HTTP_WARNING' => true,
        'HTTP_WWW_AUTHENTICATE' => true,
        'HTTP_X_FORWARDED_FOR' => true
    ];
    /** @var array<string, true> The list of header names whose values should be URL-decoded */
    private static array $headersToUrlDecode = ['HTTP_COOKIE' => true];

    /**
     * @param string[] $trustedProxyIPAddresses The list of trusted proxy IP addresses
     * @param array<string, string> $trustedHeaderNames The mapping of additional header names to trusted header names
     */
    public function __construct(protected array $trustedProxyIPAddresses = [], protected array $trustedHeaderNames = [])
    {
        $this->trustedHeaderNames = \array_merge(self::$defaultTrustedHeaderNames, $trustedHeaderNames);
    }

    /**
     * Creates a request message from PHP superglobals
     *
     * @param array<string, mixed> $server The server superglobal
     * @return IRequest The created request message
     * @throws InvalidArgumentException Thrown if any of the headers were in an invalid format
     * @throws RuntimeException Thrown if any of the headers' hash keys could not be calculated
     */
    public function createRequestFromSuperglobals(array $server): IRequest
    {
        $method = (string)($server['REQUEST_METHOD'] ?? 'GET');

        // Permit the overriding of the request method for POST requests
        if ($method === 'POST' && isset($server['X-HTTP-METHOD-OVERRIDE'])) {
            $method = (string)$server['X-HTTP-METHOD-OVERRIDE'];
        }

        $uri = $this->createUriFromSuperglobals($server);
        $headers = $this->createHeadersFromSuperglobals($server);
        $body = new StreamBody(new Stream(\fopen('php://input', 'rb')));
        $properties = $this->createProperties($server);

        return new Request($method, $uri, $headers, $body, $properties);
    }

    /**
     * Creates headers from PHP globals
     *
     * @param array<string, mixed> $server The global server array
     * @return Headers The request headers
     * @throws InvalidArgumentException Thrown if any of the headers were in an invalid format
     * @throws RuntimeException Thrown if any of the headers' hash keys could not be calculated
     */
    protected function createHeadersFromSuperglobals(array $server): Headers
    {
        $headers = new Headers();

        /** @psalm-suppress MixedAssignment The values could legitimately be mixed */
        foreach ($server as $name => $values) {
            // If this header supports multiple values and has unquoted string delimiters...
            $explodedValues = [];
            $containsMultipleValues = isset(self::$headersThatPermitMultipleValues[$name])
                && \count($explodedValues = \preg_split('/,(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/', (string)$values)) > 1;

            if ($containsMultipleValues) {
                foreach ($explodedValues as $value) {
                    $this->addHeaderValue($headers, $name, $value, true);
                }
            } else {
                $this->addHeaderValue($headers, $name, $values, false);
            }
        }

        return $headers;
    }

    /**
     * Creates properties
     *
     * @param array<string, mixed> $server The global server array
     * @return IDictionary The list of properties
     * @throws RuntimeException Thrown if any of the headers' hash keys could not be calculated
     */
    protected function createProperties(array $server): IDictionary
    {
        $properties = new HashTable();

        // Set the client IP address as a property
        if (($clientIPAddress = $this->getClientIPAddress($server)) !== null) {
            $properties->add(self::CLIENT_IP_ADDRESS_PROPERTY_NAME, $clientIPAddress);
        }

        return $properties;
    }

    /**
     * Creates a URI from PHP globals
     *
     * @param array<string, mixed> $server The global server array
     * @return Uri The URI
     * @throws InvalidArgumentException Thrown if the host is malformed
     */
    protected function createUriFromSuperglobals(array $server): Uri
    {
        $isUsingTrustedProxy = $this->isUsingTrustedProxy($server);

        if ($isUsingTrustedProxy && isset($server[$this->trustedHeaderNames['HTTP_CLIENT_PROTO']])) {
            $protoString = (string)$server[$this->trustedHeaderNames['HTTP_CLIENT_PROTO']];
            $protoArray = \explode(',', $protoString);
            /** @psalm-suppress RedundantCondition Psalm is incorrectly marking the count as redundant - bug */
            $isSecure = \count($protoArray) > 0 && \in_array(\strtolower($protoArray[0]), ['https', 'ssl', 'on'], true);
        } else {
            $isSecure = isset($server['HTTPS']) && $server['HTTPS'] !== 'off';
        }

        $rawProtocol = isset($server['SERVER_PROTOCOL']) ? \strtolower((string)$server['SERVER_PROTOCOL']) : 'http/1.1';
        $scheme = \substr($rawProtocol, 0, (\strpos($rawProtocol, '/') ?: 0)) . ($isSecure ? 's' : '');
        /** @var string|null $user */
        $user = $server['PHP_AUTH_USER'] ?? null;
        /** @var string|null $password */
        $password = $server['PHP_AUTH_PW'] ?? null;
        $port = null;

        if ($isUsingTrustedProxy) {
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

        if ($isUsingTrustedProxy && isset($server[$this->trustedHeaderNames['HTTP_CLIENT_HOST']])) {
            $hostWithPort = \explode(',', (string)$server[$this->trustedHeaderNames['HTTP_CLIENT_HOST']]);
            $hostWithPort = \trim(\end($hostWithPort));
        } else {
            $hostWithPort = (string)($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? $server['SERVER_ADDR'] ?? '');
        }

        // Remove the port from the host
        $host = \strtolower(\preg_replace("/:\d+$/", '', \trim($hostWithPort)));

        // Check for forbidden characters
        if (!empty($host) && !empty(\preg_replace("/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/", '', $host))) {
            throw new InvalidArgumentException("Invalid host \"$host\"");
        }

        $path = \parse_url('http://foo.com' . (string)($server['REQUEST_URI'] ?? ''), PHP_URL_PATH);
        $path = $path === false ? '' : ($path ?? '');
        $queryString = (string)($server['QUERY_STRING'] ?? '');

        if ($queryString === '') {
            $queryString = \parse_url('http://foo.com' . (string)($server['REQUEST_URI'] ?? ''), PHP_URL_QUERY);
            $queryString = $queryString === false || $queryString === null ? '' : $queryString;
        }

        // The "?" is simply the separator for the query string, not actually part of the query string
        $queryString = \ltrim($queryString, '?');
        $uriString = "$scheme://";

        if ($user !== null) {
            $uriString .= "$user:" . ($password ?? '') . '@';
        }

        $uriString .= $host . ($port === null ? '' : ":$port") . $path;

        if (!empty($queryString)) {
            $uriString .= "?$queryString";
        }

        return new Uri($uriString);
    }

    /**
     * Gets the client IP address
     *
     * @param array<string, mixed> $server The global server array
     * @return string|null The client IP address if one was found, otherwise null
     */
    protected function getClientIPAddress(array $server): ?string
    {
        /** @var string|null $serverRemoteAddress */
        $serverRemoteAddress = $server['REMOTE_ADDR'] ?? null;

        if ($this->isUsingTrustedProxy($server)) {
            return $serverRemoteAddress ?? null;
        }

        $ipAddresses = [];

        // RFC 7239
        if (isset($server[$this->trustedHeaderNames['HTTP_FORWARDED']])) {
            $header = (string)$server[$this->trustedHeaderNames['HTTP_FORWARDED']];
            \preg_match_all("/for=(?:\"?\[?)([a-z0-9:\.\-\/_]*)/", $header, $matches);
            $ipAddresses = $matches[1];
        } elseif (isset($server[$this->trustedHeaderNames['HTTP_CLIENT_IP']])) {
            $ipAddresses = \explode(',', (string)$server[$this->trustedHeaderNames['HTTP_CLIENT_IP']]);
            $ipAddresses = \array_map('trim', $ipAddresses);
        }

        if ($serverRemoteAddress !== null) {
            $ipAddresses[] = $serverRemoteAddress;
        }

        $fallbackIPAddresses = \count($ipAddresses) === 0 ? [] : [$ipAddresses[0]];

        foreach ($ipAddresses as $index => $ipAddress) {
            // Check for valid IP address
            if (\filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                unset($ipAddresses[$index]);
            }
        }

        $clientIPAddresses = \count($ipAddresses) === 0 ? $fallbackIPAddresses : \array_reverse($ipAddresses);

        return $clientIPAddresses[0] ?? null;
    }

    /**
     * Gets whether or not we're using a trusted proxy
     *
     * @param array<string, mixed> $server The global server array
     * @return bool True if using a trusted proxy, otherwise false
     */
    protected function isUsingTrustedProxy(array $server): bool
    {
        return \in_array($server['REMOTE_ADDR'] ?? '', $this->trustedProxyIPAddresses, true);
    }

    /**
     * Adds a header value
     *
     * @param Headers $headers The headers to add to
     * @param string $name The name of the header
     * @param mixed $value The header value to add
     * @param bool $append Whether or not to append the value
     */
    private function addHeaderValue(Headers $headers, string $name, mixed $value, bool $append): void
    {
        $decodedValue = \trim((string)(isset(self::$headersToUrlDecode[$name]) ? \urldecode((string)$value) : $value));

        if (isset(self::$specialCaseHeaders[$name])) {
            $headers->add($name, $decodedValue, $append);
        } elseif (\str_starts_with($name, 'HTTP_')) {
            // Drop the "HTTP_"
            $normalizedName = \substr($name, 5);
            $headers->add($normalizedName, $decodedValue, $append);
        }
    }
}
