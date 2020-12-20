<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http;

use Aphiria\Collections\HashTable;
use Aphiria\Collections\IDictionary;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines an HTTP request message
 */
class Request implements IRequest
{
    /** @var Headers $headers The request headers */
    protected Headers $headers;
    /** @var IDictionary The request properties */
    protected IDictionary $properties;
    /** @var array<string, bool> The list of valid HTTP methods */
    private static array $validMethods = [
        'CONNECT' => true,
        'DELETE' => true,
        'GET' => true,
        'HEAD' => true,
        'OPTIONS' => true,
        'PATCH' => true,
        'POST' => true,
        'PURGE' => true,
        'PUT' => true,
        'TRACE' => true
    ];
    /** @var array<string, bool> The list of request target types that require a Host header */
    private static array $validRequestTargetTypes = [
        RequestTargetTypes::ORIGIN_FORM => true,
        RequestTargetTypes::ABSOLUTE_FORM => true,
        RequestTargetTypes::AUTHORITY_FORM => true,
        RequestTargetTypes::ASTERISK_FORM => true
    ];
    /** @var array<string, bool> The list of valid request target types */
    private static array $requestTargetTypesWithHostHeader = [
        RequestTargetTypes::ORIGIN_FORM => true,
        // Per https://tools.ietf.org/html/rfc7230#section-5.4, this is necessary for old HTTP/1.0 proxies
        RequestTargetTypes::ABSOLUTE_FORM => true,
        RequestTargetTypes::ASTERISK_FORM => true
    ];

    /**
     * @param string $method The request method
     * @param Uri $uri The request URI
     * @param Headers|null $headers The request headers if any are set, otherwise null
     * @param IBody|null $body The request body if one is set, otherwise null
     * @param IDictionary|null $properties The request properties
     * @param string $protocolVersion The HTTP protocol version
     * @param string $requestTargetType The type of request target URI this request uses
     * @throws InvalidArgumentException Thrown if the any of the properties are not valid
     * @throws RuntimeException Thrown if any of the headers' hash keys could not be calculated
     */
    public function __construct(
        protected string $method,
        protected Uri $uri,
        Headers $headers = null,
        protected ?IBody $body = null,
        IDictionary $properties = null,
        protected string $protocolVersion = '1.1',
        protected string $requestTargetType = RequestTargetTypes::ORIGIN_FORM
    ) {
        $this->method = \strtoupper($method);
        $this->headers = $headers ?? new Headers();
        $this->properties = $properties ?? new HashTable();
        $this->validateProperties();

        /** @link https://tools.ietf.org/html/rfc7230#section-5.4 */
        if (
            isset(self::$requestTargetTypesWithHostHeader[$this->requestTargetType]) &&
            !$this->headers->containsKey('Host')
        ) {
            /** @psalm-suppress PossiblyNullReference Psalm doesn't recognize promoted properties yet - bug */
            $this->headers->add('Host', $this->uri->getAuthority(false) ?? '');
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        $startLine = "{$this->method} {$this->getRequestTarget()} HTTP/{$this->protocolVersion}";
        $headers = '';

        if (\count($this->headers) > 0) {
            $headers .= "\r\n{$this->headers}";
        }

        $serializedRequest = $startLine . $headers . "\r\n\r\n";

        if ($this->body !== null) {
            $serializedRequest .= $this->body;
        }

        return $serializedRequest;
    }

    /**
     * @inheritdoc
     */
    public function getBody(): ?IBody
    {
        return $this->body;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * @inheritdoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function getProperties(): IDictionary
    {
        return $this->properties;
    }

    /**
     * @inheritdoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritdoc
     */
    public function getUri(): Uri
    {
        return $this->uri;
    }

    /**
     * @inheritdoc
     */
    public function setBody(IBody $body): void
    {
        $this->body = $body;
    }

    /**
     * Gets the request target
     *
     * return @string The request target
     */
    private function getRequestTarget(): string
    {
        switch ($this->requestTargetType) {
            case RequestTargetTypes::ORIGIN_FORM:
                $requestTarget = $this->uri->getPath();

                /** @link https://tools.ietf.org/html/rfc7230#section-5.3.1 */
                if ($requestTarget === null || $requestTarget === '') {
                    $requestTarget = '/';
                }

                if (($queryString = $this->uri->getQueryString()) !== null && $queryString !== '') {
                    $requestTarget .= "?$queryString";
                }

                return $requestTarget;
            case RequestTargetTypes::AUTHORITY_FORM:
                return $this->uri->getAuthority(false) ?? '';
            case RequestTargetTypes::ASTERISK_FORM:
                return '*';
            case RequestTargetTypes::ABSOLUTE_FORM:
            default:
                return (string)$this->uri;
        }
    }

    /**
     * Validates all the properties
     *
     * @throws InvalidArgumentException Thrown if any of the properties are invalid
     */
    private function validateProperties(): void
    {
        if (!isset(self::$validMethods[$this->method])) {
            throw new InvalidArgumentException("Invalid HTTP method {$this->method}");
        }

        if (!isset(self::$validRequestTargetTypes[$this->requestTargetType])) {
            throw new InvalidArgumentException("Request target type {$this->requestTargetType} is invalid");
        }
    }
}
