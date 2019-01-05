<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\Serialization\ISerializer;
use Opulence\Serialization\TypeResolver;

/**
 * Defines the base class for media type formatters that use serializers to extend
 */
abstract class SerializerMediaTypeFormatter extends MediaTypeFormatter
{
    /** @var ISerializer The serializer this formatter uses */
    private $serializer;

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

        return $this->serializer->deserialize((string)$stream, $type);
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

        $serializedObject = $this->serializer->serialize($value);
        $encodedSerializedObject = \mb_convert_encoding($serializedObject, $encoding);
        $stream->write($encodedSerializedObject);
    }
}
