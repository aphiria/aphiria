<?php
namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpHeaders;

/**
 * Defines an HTTP request message
 */
class Request implements IHttpRequestMessage
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
    public function getMethod() : string
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function getProperties() : array
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function getRequestUri() : IUri
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
    public function setMethod(string $method) : void
    {
        // Todo
    }

    /**
     * @inheritdoc
     */
    public function setRequestUri(IUri $requestUri) : void
    {
        // Todo
    }
}
