<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use Opulence\Net\Collection;
use Opulence\Net\Http\HttpHeaders;
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
     * @return Collection The body form input as a collection
     */
    public function parseFormInput(IHttpRequestMessage $request) : Collection
    {
        $headers = $request->getHeaders();
        $body = $request->getBody();
        
        if (!$this->isFormUrlEncodedRequest($headers) || $body === null) {
            return new Collection();
        }

        $parsedFormInputCacheKey = spl_object_hash($body);

        if (isset($this->parsedFormInputCache[$parsedFormInputCacheKey])) {
            return $this->parsedFormInputCache[$parsedFormInputCacheKey];
        }

        $formInputArray = [];
        parse_str($body->readAsString(), $formInputArray);
        // Cache this for next time
        $formInputCollection = new Collection($formInputArray);
        $this->parsedFormInputCache[$parsedFormInputCacheKey] = $formInputCollection;

        return $formInputCollection;
    }

    /**
     * Attempts to read the request body as JSON
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return array The request body as JSON
     * @throws RuntimeException Thrown if the body could not be read as JSON
     */
    public function parseJson(IHttpRequestMessage $request) : array
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
}
