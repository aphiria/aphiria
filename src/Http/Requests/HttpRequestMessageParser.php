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
use Opulence\Collections\HashTable;
use Opulence\Collections\IDictionary;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\StringBody;
use RuntimeException;

/**
 * Defines the HTTP request message parser
 */
class HttpRequestMessageParser
{
    /** @var array The mapping of body hash IDs to their parsed form input */
    private $parsedFormInputCache = [];

    /**
     * Parses a request body as form input
     *
     * @param IHttpRequestMessage $request
     * @return IDictionary The body form input as a collection
     * @throws InvalidArgumentException Thrown if the request is not a form-URL-encoded request
     */
    public function readAsFormInput(IHttpRequestMessage $request) : IDictionary
    {
        $headers = $request->getHeaders();
        $body = $request->getBody();

        if (!$this->isFormUrlEncodedRequest($headers) || $body === null) {
            throw new InvalidArgumentException('Request is not a form URL-encoded reuqest');
        }

        $parsedFormInputCacheKey = spl_object_hash($body);

        if (isset($this->parsedFormInputCache[$parsedFormInputCacheKey])) {
            return $this->parsedFormInputCache[$parsedFormInputCacheKey];
        }

        $formInputArray = [];
        parse_str($body->readAsString(), $formInputArray);
        $kvps = [];

        foreach ($formInputArray as $key => $value) {
            $kvps[] = new KeyValuePair($key, $value);
        }

        // Cache this for next time
        $formInputs = new HashTable($kvps);
        $this->parsedFormInputCache[$parsedFormInputCacheKey] = $formInputs;

        return $formInputs;
    }

    /**
     * Parses a request as a multipart request
     * Note: This method should only be called once for best performance
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return MultipartBodyPart[] The list of uploaded files
     * @throws InvalidArgumentException Thrown if the request is not a multipart request
     */
    public function readAsMultipart(IHttpRequestMessage $request) : array
    {
        if (preg_match('/multipart\//i', $request->getHeaders()->getFirst('Content-Type')) !== 1) {
            throw new InvalidArgumentException('Request is not a multipart request');
        }

        $boundaryMatches = [];

        if (
            preg_match(
                '/boundary=(\"?)(.*)\1/',
                $request->getHeaders()->getFirst('Content-Type'),
                $boundaryMatches) !== 1
        ) {
            throw new InvalidArgumentException('Boundary is missing in Content-Type of multipart request');
        }

        $boundary = $boundaryMatches[2];
        $rawBodyParts = explode("--$boundary", $request->getBody()->readAsString());
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

    /**
     * Attempts to read the request body as JSON
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return array The request body as JSON
     * @throws InvalidArgumentException Thrown if the request is not a JSON request
     * @throws RuntimeException Thrown if the body could not be read as JSON
     */
    public function readAsJson(IHttpRequestMessage $request) : array
    {
        if (preg_match("/application\/json/i", $request->getHeaders()->getFirst('Content-Type')) !== 1) {
            throw new InvalidArgumentException('Request is not a JSON request');
        }

        $json = json_decode($request->getBody()->readAsString(), true);

        if ($json === null) {
            throw new RuntimeException('Body could not be decoded as JSON');
        }

        return $json;
    }

    /**
     * Gets whether or not a request is form URL-encoded
     *
     * @param HttpHeaders $headers The headers to parse
     * @return bool True if the request is form URL-encoded, otherwise false
     */
    private function isFormUrlEncodedRequest(HttpHeaders $headers) : bool
    {
        return mb_strpos($headers->getFirst('Content-Type'), 'application/x-www-form-urlencoded') === 0;
    }
}
