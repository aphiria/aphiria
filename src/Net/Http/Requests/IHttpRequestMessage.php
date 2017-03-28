<?php
namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\IHttpMessage;
use Opulence\Net\IUri;

/**
 * Defines the interface for HTTP request messages to implement
 */
interface IHttpRequestMessage extends IHttpMessage
{
    public function getMethod() : string;

    public function getProperties() : array;

    public function getRequestUri() : IUri;

    public function setMethod(string $method) : void;

    public function setRequestUri(IUri $requestUri) : void;
}
