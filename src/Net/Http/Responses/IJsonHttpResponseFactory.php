<?php
namespace Opulence\Net\Http\Responses;

/**
 * Defines the interface for JSON HTTP response factories to implement
 */
interface IJsonHttpResponseFactory
{
    public function createResponse($content, int $statusCode = HttpStatusCodes::HTTP_OK, array $headers = []) : IHttpResponseMessage;
}
