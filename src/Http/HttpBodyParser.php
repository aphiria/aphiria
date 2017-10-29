<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\Collections\HashTable;
use Opulence\Collections\IDictionary;
use Opulence\Collections\KeyValuePair;
use RuntimeException;

/**
 * Defines the HTTP body parser
 */
class HttpBodyParser
{
    /** @var array The mapping of body hash IDs to their parsed form input */
    private $parsedFormInputCache = [];

    /**
     * Parses a request body as form input
     *
     * @param IHttpBody|null $body The body to parse
     * @return IDictionary The body form input as a collection
     */
    public function readAsFormInput(?IHttpBody $body) : IDictionary
    {
        if ($body === null) {
            return new HashTable();
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
     * Attempts to read the request body as JSON
     *
     * @param IHttpBody|null $body The body to parse
     * @return array The request body as JSON
     * @throws RuntimeException Thrown if the body could not be read as JSON
     */
    public function readAsJson(?IHttpBody $body) : array
    {
        if ($body === null) {
            return [];
        }

        $json = json_decode($body->readAsString(), true);

        if ($json === null) {
            throw new RuntimeException('Body could not be decoded as JSON');
        }

        return $json;
    }
}
