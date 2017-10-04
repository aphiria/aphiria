<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use RuntimeException;

/**
 * Defines the HTTP request message parser
 */
class HttpRequestMessageParser
{
    /** @var array The mapping of body hash IDs to their parsed form input */
    private $parsedFormInputCache = [];

    /**
     * Parses a request for all the form input in the body
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return array The mapping of input names to values
     */
    public function getAllFormInput(IHttpRequestMessage $request) : array
    {
        return $this->parseFormInput($request->getHeaders(), $request->getBody());
    }

    /**
     * Parses a request for a form input value
     *
     * @param IHttpRequestMessage $request The request to parse
     * @param string $name The name of the input whose value we want
     * @param mixed|null $default The default value, if none was found
     * @return mixed The value of the input
     */
    public function getFormInput(IHttpRequestMessage $request, string $name, $default = null)
    {
        return $this->parseFormInput($request->getHeaders(), $request->getBody())[$name] ?? $default;
    }

    /**
     * Attempts to read the request body as JSON
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return array The request body as JSON
     * @throws RuntimeException Thrown if the body could not be read as JSON
     */
    public function readAsJson(IHttpRequestMessage $request) : array
    {
        if (preg_match("/application\/json/i", $request->getHeaders()->get('Content-Type')) !== 1) {
            return [];
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
        return mb_strpos($headers->get('Content-Type'), 'application/x-www-form-urlencoded') === 0;
    }

    /**
     * Parses form input from the body
     *
     * @param HttpHeaders $headers The header to parse
     * @param IHttpBody $body The body to parse
     * @return array The parsed form input
     */
    private function parseFormInput(HttpHeaders $headers, ?IHttpBody $body) : array
    {
        if (!$this->isFormUrlEncodedRequest($headers) || $body === null) {
            return [];
        }

        $parsedFormInputCacheKey = spl_object_hash($body);

        if (isset($this->parsedFormInputCache[$parsedFormInputCacheKey])) {
            return $this->parsedFormInputCache[$parsedFormInputCacheKey];
        }

        $allFormInput = [];
        parse_str($body->readAsString(), $allFormInput);
        // Cache this for next time
        $this->parsedFormInputCache[$parsedFormInputCacheKey] = $allFormInput;

        return $allFormInput;
    }
}
