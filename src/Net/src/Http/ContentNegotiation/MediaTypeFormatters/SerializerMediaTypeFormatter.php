<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters;

use Aphiria\Serialization\ISerializer;
use Aphiria\Serialization\SerializationException as SerializerSerializationException;
use Aphiria\Serialization\TypeResolver;
use InvalidArgumentException;
use Aphiria\IO\Streams\IStream;

/**
 * Defines the base class for media type formatters that use serializers to extend
 */
abstract class SerializerMediaTypeFormatter extends MediaTypeFormatter
{
    /** @var ISerializer The serializer this formatter uses */
    private ISerializer $serializer;

    /**
     * @param ISerializer $serializer The serializer this formatter uses
     */
    protected function __construct(ISerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function readFromStream(IStream $stream, string $type)
    {
        if (!$this->canReadType($type)) {
            throw new InvalidArgumentException(static::class . " cannot read type $type");
        }

        try {
            return $this->serializer->deserialize((string)$stream, $type);
        } catch (SerializerSerializationException $ex) {
            throw new SerializationException("Failed to read content from stream as type $type", 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function writeToStream($value, IStream $stream, ?string $encoding): void
    {
        $type = TypeResolver::resolveType($value);

        if (!$this->canWriteType($type)) {
            throw new InvalidArgumentException(static::class . " cannot write type $type");
        }

        $encoding = $encoding ?? $this->getDefaultEncoding();

        if (!$this->encodingIsSupported($encoding)) {
            throw new InvalidArgumentException("$encoding is not supported for " . static::class);
        }

        try {
            $serializedObject = $this->serializer->serialize($value);
        } catch (SerializerSerializationException $ex) {
            throw new SerializationException('Failed to write content to stream', 0, $ex);
        }

        $encodedSerializedObject = mb_convert_encoding($serializedObject, $encoding);
        $stream->write($encodedSerializedObject);
    }
}
