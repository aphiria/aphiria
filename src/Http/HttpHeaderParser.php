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
     * Parses the parameters (semi-colon delimited values for a header) for the first value of a header
     *
     * @param string $value The value to parse
     * @return IImmutableDictionary The dictionary of parameters for the first value
     */
    public function parseParameters(string $value)
    {
        $kvps = [];

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
