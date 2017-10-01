<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;

/**
 * Defines an HTTP response message
 */
class Response implements IHttpResponseMessage
{
    /** @var IHttpBody The body of the response */
    protected $body = null;
    /** @var HttpHeaders The list of response headers */
    protected $headers = null;
    /** @var string|null The response reason phrase if there is one, otherwise null */
    protected $reasonPhrase = null;
    /** @var int The response status code */
    protected $statusCode = HttpStatusCodes::HTTP_OK;

    /**
     * @param int $statusCode The response status code
     * @param HttpHeaders|null $headers The list of response headers
     * @param IHttpBody|null $body The response body
     */
    public function __construct(
        int $statusCode = HttpStatusCodes::HTTP_OK,
        HttpHeaders $headers = null,
        IHttpBody $body = null
    ) {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = ResponseStatusCodes::getDefaultReasonPhrase($this->statusCode);
        $this->headers = $headers ?? new HttpHeaders;
        $this->body = $body;
    }

    /**
     * @inheritdoc
     */
    public function getBody() : IHttpBody
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
    public function getReasonPhrase() : ?string
    {
        return $this->reasonPhrase;
    }

    /**
     * @inheritdoc
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
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
    public function setStatusCode(int $statusCode, ?string $reasonPhrase = null) : void
    {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase ?? ResponseStatusCodes::getDefaultReasonPhrase($this->statusCode);
    }
}
