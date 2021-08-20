<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Collections\HashTable;
use Aphiria\Collections\IDictionary;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\MultipartBody;
use Aphiria\Net\Http\MultipartBodyPart;
use Aphiria\Net\Http\StringBody;
use finfo;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * Defines the HTTP body parser
 */
class BodyParser
{
    /** @var array<string, HashTable<string, mixed>> The mapping of body hash IDs to their parsed form input */
    private array $parsedFormInputCache = [];
    /** @var array<string, string> The mapping of body hash IDs to their parsed MIME types */
    private array $parsedMimeTypeCache = [];

    /**
     * Gets the MIME type of the body
     *
     * @param IBody|null $body The body whose MIME type we want, or null if there is no body
     * @return string|null The mime type if one is set, otherwise null
     * @throws RuntimeException Thrown if the MIME type could not be determined
     */
    public function getMimeType(?IBody $body): ?string
    {
        if ($body === null) {
            return null;
        }

        $parsedMimeTypeCacheKey = \spl_object_hash($body);

        if (isset($this->parsedMimeTypeCache[$parsedMimeTypeCacheKey])) {
            return $this->parsedMimeTypeCache[$parsedMimeTypeCacheKey];
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);

        if (($mimeType = $fileInfo->buffer($body->readAsString())) === false) {
            // Cannot test failing to writing data into a file buffer
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Could not determine mime type of body');
            // @codeCoverageIgnoreEnd
        }

        // Cache this for next time
        $this->parsedMimeTypeCache[$parsedMimeTypeCacheKey] = $mimeType;

        return $mimeType;
    }

    /**
     * Parses a request body as form input
     *
     * @param IBody|null $body The body to parse
     * @return IDictionary<string, mixed> The body form input as a collection
     */
    public function readAsFormInput(?IBody $body): IDictionary
    {
        if ($body === null) {
            /** @var HashTable<string, mixed> $input */
            $input = new HashTable();

            return $input;
        }

        $parsedFormInputCacheKey = \spl_object_hash($body);

        if (isset($this->parsedFormInputCache[$parsedFormInputCacheKey])) {
            return $this->parsedFormInputCache[$parsedFormInputCacheKey];
        }

        $formInputArray = [];
        \parse_str($body->readAsString(), $formInputArray);
        $kvps = [];

        /** @psalm-suppress MixedAssignment Value here really could be mixed */
        foreach ($formInputArray as $key => $value) {
            $kvps[] = new KeyValuePair((string)$key, $value);
        }

        // Cache this for next time
        $formInputs = new HashTable($kvps);
        $this->parsedFormInputCache[$parsedFormInputCacheKey] = $formInputs;

        return $formInputs;
    }

    /**
     * Attempts to read the request body as JSON
     *
     * @param IBody|null $body The body to parse
     * @return array<array-key, mixed> The request body as JSON
     * @throws RuntimeException Thrown if the body could not be read as JSON
     */
    public function readAsJson(?IBody $body): array
    {
        if ($body === null) {
            return [];
        }

        try {
            /** @var array<array-key, mixed> $json */
            $json = \json_decode($body->readAsString(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new RuntimeException('Body could not be decoded as JSON', 0, $ex);
        }

        return $json;
    }

    /**
     * Reads the body as a multipart body
     *
     * @param IBody|null $body The body to parse
     * @param string $boundary The boundary that separates the multipart body parts
     * @return MultipartBody|null The multipart body if it could be read as a multipart body, otherwise null
     * @throws InvalidArgumentException Thrown if the body parts were invalid
     * @throws RuntimeException Thrown if the headers' hash keys could not be calculated
     */
    public function readAsMultipart(?IBody $body, string $boundary): ?MultipartBody
    {
        if ($body === null) {
            return null;
        }

        $rawBodyParts = \explode("--$boundary", $body->readAsString());
        // The first part will be empty, and the last will be "--".  Remove them.
        \array_shift($rawBodyParts);
        \array_pop($rawBodyParts);
        $parsedBodyParts = [];

        foreach ($rawBodyParts as $rawBodyPart) {
            $headerStartIndex = \strlen("\r\n");
            $headerEndIndex = \strpos($rawBodyPart, "\r\n\r\n") ?: 0;
            $bodyStartIndex = $headerEndIndex + \strlen("\r\n\r\n");
            $bodyEndIndex = \strlen($rawBodyPart) - \strlen("\r\n");
            $rawHeaders = \explode("\r\n", \substr($rawBodyPart, $headerStartIndex, $headerEndIndex - $headerStartIndex));
            $parsedHeaders = new Headers();

            foreach ($rawHeaders as $headerLine) {
                [$headerName, $headerValue] = \explode(':', $headerLine, 2);
                $parsedHeaders->add(\trim($headerName), \trim($headerValue));
            }

            $body = new StringBody(\substr($rawBodyPart, $bodyStartIndex, $bodyEndIndex - $bodyStartIndex));
            $parsedBodyParts[] = new MultipartBodyPart($parsedHeaders, $body);
        }

        return new MultipartBody($parsedBodyParts, $boundary);
    }
}
