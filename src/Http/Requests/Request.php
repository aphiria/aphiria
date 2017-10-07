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
use Opulence\Net\Collection;
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
    /** @var Collection The request properties */
    protected $properties = null;
    /** @var string The HTTP protocol version */
    protected $protocolVersion = '';
    /** @var The list of valid HTTP methods */
    private static $validMethod = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PURGE',
        'PUT',
        'TRACE'
    ];

    /**
     * @param string $method The request method
     * @param HttpHeaders $headers The request headers
     * @param IHttpBody $body The request body
     * @param Uri $uri The request URI
     * @param Collection|null $properties The request properties
     * @param string $protocolVersion The HTTP protocol version
     */
    public function __construct(
        string $method,
        HttpHeaders $headers,
        IHttpBody $body,
        Uri $uri,
        Collection $properties = null,
        string $protocolVersion = '1.1'
    ) {
        $this->setMethod($method);
        $this->headers = $headers;
        $this->body = $body;
        $this->uri = $uri;
        $this->properties = $properties ?? new Collection();
        $this->protocolVersion = $protocolVersion;
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
    public function getProperties() : Collection
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
     * @inheritdoc
     */
    protected function setMethod(string $method) : void
    {
        $uppercaseMethod = strtoupper($method);

        if (!in_array($uppercaseMethod, self::$validMethod)) {
            throw new InvalidArgumentException("Invalid HTTP method $method");
        }

        $this->method = $method;
    }
}
