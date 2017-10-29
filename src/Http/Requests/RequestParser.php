<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use InvalidArgumentException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\StringBody;

/**
 * Defines the HTTP request message parser
 */
class RequestParser
{
    /**
     * Parses a request as a multipart request
     * Note: This method should only be called once for best performance
     *
     * @param IHttpRequestMessage|MultipartBodyPart $request The request or multipart body part to parse
     * @return MultipartBodyPart[] The list of uploaded files
     * @throws InvalidArgumentException Thrown if the request is not a multipart request
     */
    public function readAsMultipart($request) : array
    {
        if (!$request instanceof IHttpRequestMessage && !$request instanceof MultipartBodyPart) {
            throw new InvalidArgumentException(
                'Request must be of type ' . IHttpRequestMessage::class . ' or ' . MultipartBodyPart::class
            );
        }

        $headers = $request->getHeaders();
        $body = $request->getBody();

        if (preg_match('/multipart\//i', $headers->getFirst('Content-Type')) !== 1) {
            throw new InvalidArgumentException('Request is not a multipart request');
        }

        if ($body === null) {
            return [];
        }

        $boundaryMatches = [];

        if (
            preg_match(
                '/boundary=(\"?)(.*)\1/',
                $headers->getFirst('Content-Type'),
                $boundaryMatches) !== 1
        ) {
            throw new InvalidArgumentException('Boundary is missing in Content-Type in multipart request');
        }

        $boundary = $boundaryMatches[2];
        $rawBodyParts = explode("--$boundary", $body->readAsString());
        // The first part will be empty, and the last will be "--".  Remove them.
        array_shift($rawBodyParts);
        array_pop($rawBodyParts);
        $parsedBodyParts = [];

        foreach ($rawBodyParts as $rawBodyPart) {
            $headerStartIndex = strlen("\r\n");
            $headerEndIndex = strpos($rawBodyPart, "\r\n\r\n");
            $bodyStartIndex = $headerEndIndex + strlen("\r\n\r\n");
            $bodyEndIndex = strlen($rawBodyPart) - strlen("\r\n");
            $rawHeaders = explode("\r\n", substr($rawBodyPart, $headerStartIndex, $headerEndIndex - $headerStartIndex));
            $parsedHeaders = new HttpHeaders();

            foreach ($rawHeaders as $headerLine) {
                [$headerName, $headerValue] = explode(':', $headerLine, 2);
                $parsedHeaders->add(trim($headerName), trim($headerValue));
            }

            $body = new StringBody(substr($rawBodyPart, $bodyStartIndex, $bodyEndIndex - $bodyStartIndex));
            $parsedBodyParts[] = new MultipartBodyPart($parsedHeaders, $body);
        }

        return $parsedBodyParts;
    }
}
