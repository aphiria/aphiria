<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use Opulence\IO\Streams\IStream;
use Opulence\Serialization\JsonSerializer;

/**
 * Defines the JSON media type formatter
 */
class JsonMediaTypeFormatter implements IMediaTypeFormatter
{
    /** @var array The list of supported character encodings */
    private static $supportedEncodings = ['utf-8'];
    /** @var array The list of supported media types */
    private static $supportedMediaTypes = ['application/json', 'text/json'];
    /** @var JsonSerializer The JSON serializer */
    private $serializer;

    /**
     * @param JsonSerializer $serializer The JSON serializer
     */
    public function __construct(JsonSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedEncodings(): array
    {
        return self::$supportedEncodings;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedMediaTypes(): array
    {
        return self::$supportedMediaTypes;
    }

    /**
     * @inheritdoc
     */
    public function readFromStream(string $type, IStream $stream, bool $readAsArrayOfType = false)
    {
        $formattedType = $readAsArrayOfType ? $type . '[]' : $type;

        return $this->serializer->deserialize((string)$stream, $formattedType);
    }

    /**
     * @inheritdoc
     */
    public function writeToStream($object, IStream $stream): void
    {
        $serializedObject = $this->serializer->serialize($object);
        $stream->write($serializedObject);
    }
}
