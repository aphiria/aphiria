<?php
namespace Opulence\Net\Http\Responses;

use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpHeaders;

/**
 * Defines an HTTP response message
 */
class Response implements IHttpResponseMessage
{
    /**
     * @inheritdoc
     */
    public function getBody() : IHttpBody
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function getHeaders() : IHttpHeaders
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function getOutputStream() : IStream
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function getReasonPhrase() : ?string
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function getStatusCode() : int
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function setBody(IHttpBody $body) : void
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function setStatusCode(int $statusCode, ?string $reasonPhrase = null) : void
    {
        // Todo
    }
}
