<?php
namespace Opulence\Net\Http\Responses;

use Opulence\IO\Streams\IStream;

/**
 * Defines the interface for stream HTTP response factories to implement
 */
interface IStreamHttpResponseFactory
{
    public function createResponse(IStream $stream, int $statusCode = HttpStatusCodes::HTTP_OK, array $headers = []) : IHttpResponseMessage;
}