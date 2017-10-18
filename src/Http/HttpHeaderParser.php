<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\Collections\IImmutableDictionary;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;

/**
 * Defines the HTTP header parser
 */
class HttpHeaderParser
{
    /** The list of trimmed characters from parameters */
    private const PARAMETER_TRIMMED_CHARS = "\"'  \n\t\r";
    /** The regex used to split parameter values */
    private const PARAMETER_SPLIT_REGEX = '/;(?=([^"]*"[^"]*")*[^"]*$)/';
    /** The regex used to split a parameter into a key-value pair */
    private const PARAMETER_KEY_VALUE_REGEX = '/<[^>]+>|[^=]+/';

    /**
     * Parses the parameters (semi-colon delimited values for a header) for all values of a header
     *
     * @param HttpHeaders $headers The headers to parse
     * @param string $name The name of the header whose parameters we want
     * @return IImmutableDictionary[] The dictionary of parameters for each value of the header
     */
    public function parseParametersForAllValues(HttpHeaders $headers, string $name)
    {
        if (!$headers->containsKey($name)) {
            return [new ImmutableHashTable([])];
        }

        $parameters = [];
        $values = [];
        $headers->tryGet($name, $values);

        foreach ($values as $value) {
            $kvps = $this->parseParameters($value);

            if (count($kvps) > 0) {
                $parameters[] = $kvps;
            }
        }

        return $parameters;
    }

    /**
     * Parses the parameters (semi-colon delimited values for a header) for the first value of a header
     *
     * @param HttpHeaders $headers The headers to parse
     * @param string $name The name of the header whose parameters we want
     * @return IImmutableDictionary The dictionary of parameters for the first value
     */
    public function parseParametersForFirstValue(HttpHeaders $headers, string $name)
    {
        if (!$headers->containsKey($name)) {
            return new ImmutableHashTable([]);
        }

        return $this->parseParameters($headers->getFirst($name));
    }

    /**
     * Parses the parameters of a header value into a dictionary
     *
     * @param string|int|float $value The value to parse
     * @return IImmutableDictionary The dictionary of parameter name => value pairs
     */
    private function parseParameters($value) : IImmutableDictionary
    {
        foreach (preg_split(self::PARAMETER_SPLIT_REGEX, $value) as $kvp) {
            $matches = [];

            // Split the parameters into names and values
            if (preg_match_all(self::PARAMETER_KEY_VALUE_REGEX, $kvp, $matches)) {
                $key = trim($matches[0][0], self::PARAMETER_TRIMMED_CHARS);

                if (isset($matches[0][1])) {
                    $value = trim($matches[0][1], self::PARAMETER_TRIMMED_CHARS);
                } else {
                    $value = null;
                }

                $kvps[] = new KeyValuePair($key, $value);
            }
        }

        return new ImmutableHashTable($kvps);
    }
}
