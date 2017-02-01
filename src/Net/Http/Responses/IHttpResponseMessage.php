<?php
namespace Opulence\Net\Http\Responses;

use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\IHttpMessage;

/**
 * Defines the interface for HTTP response messages to implement
 */
interface IHttpResponseMessage extends IHttpMessage
{
    public function getOutputStream() : IStream;
    
    public function getReasonPhrase() : ?string;
    
    public function getStatusCode() : int;
    
    public function setStatusCode(int $statusCode, ?string $reasonPhrase = null) : void;
}
