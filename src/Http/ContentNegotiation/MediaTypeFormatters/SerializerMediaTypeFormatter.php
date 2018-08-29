<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
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
    public function canReadType(string $type): bool
    {
        return \class_exists($type);
    }

    /**
     * @inheritdoc
     */
    public function canWriteType(string $type): bool
    {
        return \class_exists($type);
    }

    /**
     * @inheritdoc
     */
    public function readFromStream(IStream $stream, string $type, bool $readAsArrayOfType = false)
    {
        if (!$this->canReadType($type)) {
            throw new InvalidArgumentException(static::class . ' can only read objects');
        }

        $formattedType = $readAsArrayOfType ? $type . '[]' : $type;

        return $this->serializer->deserialize((string)$stream, $formattedType);
    }

    /**
     * @inheritdoc
     */
    public function writeToStream($value, IStream $stream, ?string $encoding): void
    {
        if (!$this->canWriteType(TypeResolver::resolveType($value))) {
            throw new InvalidArgumentException(static::class . ' can only write objects');
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
