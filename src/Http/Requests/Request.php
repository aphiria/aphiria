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
use Opulence\Collections\HashTable;
use Opulence\Collections\IDictionary;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Uri;

/**
 * Defines an HTTP request message
 */
class Request implements IHttpRequestMessage
{
    /** @var string The request method */
    protected $method = '';
    /** @var HttpHeaders The request headers */
    protected $headers = null;
    /** @var IHttpBody|null The request body if there is one, otherwise null */
    protected $body = null;
    /** @var Uri The request URI */
    protected $uri = null;
    /** @var IDictionary The request properties */
    protected $properties = null;
    /** @var string The HTTP protocol version */
    protected $protocolVersion = '';
    /** @var string The type of request target URI this request uses */
    protected $requestTargetType = RequestTargetTypes::ORIGIN_FORM;
    /** @var array The list of valid HTTP methods */
    private static $validMethods = [
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
    /** @var array The list of request target types that require a Host header */
    private static $validRequestTargetTypes = [
        RequestTargetTypes::ORIGIN_FORM => true,
        RequestTargetTypes::ABSOLUTE_FORM => true,
        RequestTargetTypes::AUTHORITY_FORM => true,
        RequestTargetTypes::ASTERISK_FORM => true
    ];
    /** @var array The list of valid request target types */
    private static $requestTargetTypesWithHostHeader = [
        RequestTargetTypes::ORIGIN_FORM => true,
        // Per https://tools.ietf.org/html/rfc7230#section-5.4, this is necessary for old HTTP/1.0 proxies
        RequestTargetTypes::ABSOLUTE_FORM => true,
        RequestTargetTypes::ASTERISK_FORM => true
    ];

    /**
     * @param string $method The request method
     * @param Uri $uri The request URI
     * @param HttpHeaders|null $headers The request headers if any are set, otherwise null
     * @param IHttpBody $body The request body
     * @param IDictionary|null $properties The request properties
     * @param string $protocolVersion The HTTP protocol version
     * @param string $requestTargetType The type of request target URI this request uses
     */
    public function __construct(
        string $method,
        Uri $uri,
        HttpHeaders $headers = null,
        ?IHttpBody $body = null,
        IDictionary $properties = null,
        string $protocolVersion = '1.1',
        string $requestTargetType = RequestTargetTypes::ORIGIN_FORM
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = $headers ?? new HttpHeaders();
        $this->body = $body;
        $this->properties = $properties ?? new HashTable();
        $this->protocolVersion = $protocolVersion;
        $this->requestTargetType = $requestTargetType;
        $this->validateProperties();

        /** @link https://tools.ietf.org/html/rfc7230#section-5.4 */
        if (
            !$this->headers->containsKey('Host') &&
            isset(self::$requestTargetTypesWithHostHeader[$this->requestTargetType])
        ) {
            $this->headers->add('Host', $this->uri->getAuthority(false) ?? '');
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString() : string
    {
        $startLine = "{$this->method} {$this->getRequestTarget()} HTTP/{$this->protocolVersion}";
        $headers = '';

        foreach ($this->headers as $kvp) {
            $headers .= "\r\n{$kvp->getKey()}: " . implode(', ', $kvp->getValue());
        }

        $request = $startLine . $headers . "\r\n\r\n";

        if ($this->body !== null) {
            $request .= (string)$this->getBody();
        }

        return $request;
    }

    /**
     * @inheritdoc
     */
    public function getBody() : ?IHttpBody
    {
        return $this->body;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders() : HttpHeaders
    {
        return $this->headers;
    }

    /**
     * @inheritdoc
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function getProperties() : IDictionary
    {
        return $this->properties;
    }

    /**
     * @inheritdoc
     */
    public function getProtocolVersion() : string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritdoc
     */
    public function getUri() : Uri
    {
        return $this->uri;
    }

    /**
     * @inheritdoc
     */
    public function setBody(IHttpBody $body) : void
    {
        $this->body = $body;
    }

    /**
     * Gets the request target
     *
     * return @string The request target
     */
    private function getRequestTarget() : string
    {
        switch ($this->requestTargetType) {
            case RequestTargetTypes::ORIGIN_FORM:
                $requestTarget = $this->uri->getPath();

                /** @link https://tools.ietf.org/html/rfc7230#section-5.3.1 */
                if ($requestTarget === null || strlen($requestTarget) === 0) {
                    $requestTarget = '/';
                }

                if (($queryString = $this->uri->getQueryString()) !== null && strlen($queryString) > 0) {
                    $requestTarget .= "?$queryString";
                }

                return $requestTarget;
            case RequestTargetTypes::ABSOLUTE_FORM:
                return (string)$this->uri;
            case RequestTargetTypes::AUTHORITY_FORM:
                return $this->uri->getAuthority(false) ?? '';
            case RequestTargetTypes::ASTERISK_FORM:
                return '*';
            default:
                // Shouldn't happen
                return '';
        }
    }

    /**
     * Validates all the properties
     *
     * @throws InvalidArgumentException Thrown if any of the properties are invalid
     */
    private function validateProperties() : void
    {
        if (!isset(self::$validMethods[$this->method])) {
            throw new InvalidArgumentException("Invalid HTTP method {$this->method}");
        }

        if (!isset(self::$validRequestTargetTypes[$this->requestTargetType])) {
            throw new InvalidArgumentException("Request target type {$this->requestTargetType} is invalid");
        }
    }
}
