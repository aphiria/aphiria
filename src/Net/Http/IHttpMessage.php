<?php
namespace Opulence\Net\Http;

/**
 * Defines the interface for all HTTP messages
 */
interface IHttpMessage
{
    public function getBody() : IHttpBody;

    public function getHeaders() : IHttpHeaders;

    public function setBody(IHttpBody $body) : void;
}
