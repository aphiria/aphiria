<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Http\Formatting;

use finfo;
use InvalidArgumentException;
use Opulence\Collections\HashTable;
use Opulence\Collections\IDictionary;
use Opulence\Collections\KeyValuePair;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\IHttpBody;
use Aphiria\Net\Http\MultipartBody;
use Aphiria\Net\Http\MultipartBodyPart;
use Aphiria\Net\Http\StringBody;
use RuntimeException;

/**
 * Defines the HTTP body parser
 */
class HttpBodyParser
{
    /** @var array The mapping of body hash IDs to their parsed form input */
    private $parsedFormInputCache = [];
    /** @var array The mapping of body hash IDs to their parsed MIME types */
    private $parsedMimeTypeCache = [];

    /**
     * Gets the MIME type of the body
     *
     * @param IHttpBody $body The body whose MIME type we want
     * @return string|null The mime type if one is set, otherwise null
     * @throws RuntimeException Thrown if the MIME type could not be determined
     */
    public function getMimeType(?IHttpBody $body): ?string
    {
        if ($body === null) {
            return null;
        }

        $parsedMimeTypeCacheKey = spl_object_hash($body);

        if (isset($this->parsedMimeTypeCache[$parsedMimeTypeCacheKey])) {
            return $this->parsedMimeTypeCache[$parsedMimeTypeCacheKey];
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);

        if (($mimeType = $fileInfo->buffer($body->readAsString())) === false) {
            throw new RuntimeException('Could not determine mime type of body');
        }

        // Cache this for next time
        $this->parsedMimeTypeCache[$parsedMimeTypeCacheKey] = $mimeType;

        return $mimeType;
    }

    /**
     * Parses a request body as form input
     *
     * @param IHttpBody|null $body The body to parse
     * @return IDictionary The body form input as a collection
     */
    public function readAsFormInput(?IHttpBody $body): IDictionary
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
    public function readAsJson(?IHttpBody $body): array
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

    /**
     * Reads the body as a multipart body
     *
     * @param IHttpBody|null $body The body to parse
     * @param string $boundary The boundary that separates the multipart body parts
     * @return MultipartBody|null The multipart body if it could be read as a multipart body, otherwise null
     * @throws InvalidArgumentException Thrown if the body parts were invalid
     * @throws RuntimeException Thrown if the headers' hash keys could not be calculated
     */
    public function readAsMultipart(?IHttpBody $body, string $boundary): ?MultipartBody
    {
        if ($body === null) {
            return null;
        }

        $rawBodyParts = explode("--$boundary", $body->readAsString());
        // The first part will be empty, and the last will be "--".  Remove them.
        array_shift($rawBodyParts);
        array_pop($rawBodyParts);
        $parsedBodyParts = [];

        foreach ($rawBodyParts as $rawBodyPart) {
            $headerStartIndex = \strlen("\r\n");
            $headerEndIndex = \strpos($rawBodyPart, "\r\n\r\n");
            $bodyStartIndex = $headerEndIndex + \strlen("\r\n\r\n");
            $bodyEndIndex = \strlen($rawBodyPart) - \strlen("\r\n");
            $rawHeaders = explode("\r\n", substr($rawBodyPart, $headerStartIndex, $headerEndIndex - $headerStartIndex));
            $parsedHeaders = new HttpHeaders();

            foreach ($rawHeaders as $headerLine) {
                [$headerName, $headerValue] = explode(':', $headerLine, 2);
                $parsedHeaders->add(trim($headerName), trim($headerValue));
            }

            $body = new StringBody(substr($rawBodyPart, $bodyStartIndex, $bodyEndIndex - $bodyStartIndex));
            $parsedBodyParts[] = new MultipartBodyPart($parsedHeaders, $body);
        }

        return new MultipartBody($parsedBodyParts, $boundary);
    }
}
