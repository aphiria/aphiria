<?php
namespace Opulence\Net\Http\Responses;

/**
 * Defines the interface for redirect HTTP response factories to implement
 */
interface IRedirectHttpResponseFactory
{
    public function createResponse(string $uri, int $statusCode = HttpStatusCodes::HTTP_FOUND, array $headers = []) : IHttpResponseMessage;
}