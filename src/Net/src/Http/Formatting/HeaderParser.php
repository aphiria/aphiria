<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the HTTP header parser
 */
class HeaderParser
{
    /** @const The list of trimmed characters from parameters */
    private const PARAMETER_TRIMMED_CHARS = "\"'  \n\t\r";
    /** @const The regex used to split parameter values */
    private const PARAMETER_SPLIT_REGEX = '/;(?=([^"]*"[^"]*")*[^"]*$)/';
    /** @const The regex used to split a parameter into a key-value pair */
    private const PARAMETER_KEY_VALUE_REGEX = '/<[^>]+>|[^=]+/';

    /**
     * Gets whether or not the headers have a JSON content type
     *
     * @param Headers $headers The headers to parse
     * @return bool True if the message has a JSON content type, otherwise false
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function isJson(Headers $headers): bool
    {
        $contentType = null;
        $headers->tryGetFirst('Content-Type', $contentType);

        return \is_string($contentType) && \preg_match("/application\/json/i", $contentType) === 1;
    }

    /**
     * Gets whether or not the message is a multipart message
     *
     * @param Headers $headers The headers to parse
     * @return bool True if the request is a multipart message, otherwise false
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function isMultipart(Headers $headers): bool
    {
        $contentType = null;
        $headers->tryGetFirst('Content-Type', $contentType);

        return \is_string($contentType) && \preg_match("/multipart\//i", $contentType) === 1;
    }

    /**
     * Parses the Content-Type header
     *
     * @param Headers $headers The request headers to parse
     * @return ContentTypeHeaderValue|null The parsed header if one exists, otherwise null
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseContentTypeHeader(Headers $headers): ?ContentTypeHeaderValue
    {
        if (!$headers->containsKey('Content-Type')) {
            return null;
        }

        $contentTypeHeaderParameters = $this->parseParameters($headers, 'Content-Type');
        $contentType = (string)$contentTypeHeaderParameters->getKeys()[0];

        return new ContentTypeHeaderValue($contentType, $contentTypeHeaderParameters);
    }

    /**
     * Parses the parameters (semi-colon delimited values for a header) for the first value of a header
     *
     * @param Headers $headers The headers to parse
     * @param string $headerName The name of the header whose parameters we're parsing
     * @param int $index The index of the header value to parse
     * @return IImmutableDictionary<string, string|null> The dictionary of parameters for the first value
     */
    public function parseParameters(Headers $headers, string $headerName, int $index = 0): IImmutableDictionary
    {
        $headerValues = [];

        if (!$headers->tryGet($headerName, $headerValues) || !isset($headerValues[$index])) {
            /** @var ImmutableHashTable<string, string|null> $parameters */
            $parameters = new ImmutableHashTable([]);

            return $parameters;
        }

        $kvps = [];

        /** @var list<string> $headerValues */
        foreach (\preg_split(self::PARAMETER_SPLIT_REGEX, (string)$headerValues[$index]) as $kvp) {
            $matches = [];

            // Split the parameters into names and values
            if (\preg_match_all(self::PARAMETER_KEY_VALUE_REGEX, $kvp, $matches)) {
                $key = \trim($matches[0][0], self::PARAMETER_TRIMMED_CHARS);
                $value = isset($matches[0][1]) ? \trim($matches[0][1], self::PARAMETER_TRIMMED_CHARS) : null;
                $kvps[] = new KeyValuePair($key, $value);
            }
        }

        return new ImmutableHashTable($kvps);
    }
}
