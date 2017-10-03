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

/**
 * Defines the HTTP request message parser
 */
class HttpRequestMessageParser
{
    /** @var array The mapping of body hash IDs to their parsed form data */
    private $parsedFormDataCache = [];

    /**
     * Parses a request for form data in the body
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return array The mapping of input names to values
     */
    public function getFormData(IHttpRequestMessage $request) : array
    {
        return $this->parseFormData($request->getHeaders(), $request->getBody());
    }

    /**
     * Parses a request for an input value
     *
     * @param IHttpRequestMessage $request The request to parse
     * @param string $name The name of the input whose value we want
     * @param mixed|null $default The default value, if none was found
     * @return mixed The value of the input
     */
    public function getInput(IHttpRequestMessage $request, string $name, $default = null)
    {
        return $this->parseFormData($request->getHeaders(), $request->getBody())[$name] ?? $default;
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
     * Parses form data from the body
     *
     * @param HttpHeaders $headers The header to parse
     * @param IHttpBody $body The body to parse
     * @return array The parsed form data
     */
    private function parseFormData(HttpHeaders $headers, IHttpBody $body) : array
    {
        if (!$this->isFormUrlEncodedRequest($headers)) {
            return [];
        }

        $parsedFormDataCacheKey = spl_object_hash($body);

        if (isset($this->parsedFormDataCache[$parsedFormDataCacheKey])) {
            return $this->parsedFormDataCache[$parsedFormDataCacheKey];
        }

        $formData = [];
        parse_str($body->readAsString(), $formData);
        // Cache this for next time
        $this->parsedFormDataCache[$parsedFormDataCacheKey] = $formData;

        return $formData;
    }
}
