<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpHeaders;
use Opulence\Net\Uri;

/**
 * Defines an HTTP request message
 */
class Request implements IHttpRequestMessage
{
    /** @var string The request method */
    protected $method = '';
    /** @var IHttpHeaders The request headers */
    protected $headers = null;
    /** @var IHttpBody The request body */
    protected $body = null;
    /** @var Uri The request URI */
    protected $uri = null;
    /** @var array The request properties */
    protected $properties = [];
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
     * @param IHttpHeaders $headers The request headers
     * @param IHttpBody $body The request body
     * @param Uri $uri The request URI
     * @param array $properties The request properties
     */
    public function __construct(string $method, IHttpHeaders $headers, IHttpBody $body, Uri $uri, array $properties = [])
    {
        $this->method = $this->setMethod($method);
        $this->headers = $headers;
        $this->body = $body;
        $this->uri = $uri;
        $this->properties = $properties;
    }

    /**
     * @inheritdoc
     */
    public function getBody(): IHttpBody
    {
        return $this->body;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders(): IHttpHeaders
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
    public function getProperties(): array
    {
        return $this->properties;
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
    public function setBody(IHttpBody $body): void
    {
        $this->body = $body;
    }

    /**
     * @inheritdoc
     */
    public function setMethod(string $method): void
    {
        $uppercaseMethod = strtoupper($method);
        
        if (!in_array($uppercaseMethod, self::$validMethod)) {
            throw new InvalidArgumentException("Invalid HTTP method $method");
        }
                
        $this->method = $method;
    }

    /**
     * @inheritdoc
     */
    public function setUri(Uri $uri): void
    {
        $this->uri = $uri;
    }
}
