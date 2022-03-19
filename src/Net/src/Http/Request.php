<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
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
    /** @var string The request method */
    protected readonly string $method;
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

    /**
     * @param string $method The request method
     * @param Uri $uri The request URI
     * @param Headers $headers The request headers if any are set, otherwise null
     * @param IBody|null $body The request body if one is set, otherwise null
     * @param IDictionary<string, mixed> $properties The request properties
     * @param string $protocolVersion The HTTP protocol version
     * @param RequestTargetType $requestTargetType The type of request target URI this request uses
     * @throws InvalidArgumentException Thrown if any of the properties are not valid
     * @throws RuntimeException Thrown if any of the headers' hash keys could not be calculated
     */
    public function __construct(
        string $method,
        protected readonly Uri $uri,
        protected readonly Headers $headers = new Headers(),
        protected ?IBody $body = null,
        protected readonly IDictionary $properties = new HashTable(),
        protected readonly string $protocolVersion = '1.1',
        protected readonly RequestTargetType $requestTargetType = RequestTargetType::OriginForm
    ) {
        $this->method = \strtoupper($method);
        $this->validateProperties();
        $this->addHostHeaderIfNecessary();
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
     * Adds the host header if it's necessary per the request target type
     *
     * @link https://tools.ietf.org/html/rfc7230#section-5.4
     */
    private function addHostHeaderIfNecessary(): void
    {
        $requiresHostHeader = match ($this->requestTargetType) {
            RequestTargetType::OriginForm, RequestTargetType::AsteriskForm, RequestTargetType::AbsoluteForm => true,
            default => false
        };

        if ($requiresHostHeader && !$this->headers->containsKey('Host')) {
            $this->headers->add('Host', $this->uri->getAuthority(false) ?? '');
        }
    }

    /**
     * Gets the request target
     *
     * return @string The request target
     */
    private function getRequestTarget(): string
    {
        switch ($this->requestTargetType) {
            case RequestTargetType::OriginForm:
                $requestTarget = $this->uri->path;

                /** @link https://tools.ietf.org/html/rfc7230#section-5.3.1 */
                if ($requestTarget === null || $requestTarget === '') {
                    $requestTarget = '/';
                }

                if (($queryString = $this->uri->queryString) !== null && $queryString !== '') {
                    $requestTarget .= "?$queryString";
                }

                return $requestTarget;
            case RequestTargetType::AuthorityForm:
                return $this->uri->getAuthority(false) ?? '';
            case RequestTargetType::AsteriskForm:
                return '*';
            case RequestTargetType::AbsoluteForm:
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
    }
}
