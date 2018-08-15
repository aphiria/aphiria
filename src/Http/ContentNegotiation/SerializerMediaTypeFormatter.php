<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ContentNegotiation;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\Serialization\ISerializer;

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
    public function readFromStream(IStream $stream, string $type, bool $readAsArrayOfType = false)
    {
        $formattedType = $readAsArrayOfType ? $type . '[]' : $type;

        return $this->serializer->deserialize((string)$stream, $formattedType);
    }

    /**
     * @inheritdoc
     */
    public function writeToStream($object, IStream $stream, ?string $encoding): void
    {
        $encoding = $encoding ?? $this->getDefaultEncoding();

        if (!$this->encodingIsSupported($encoding)) {
            throw new InvalidArgumentException("$encoding is not supported for " . static::class);
        }

        $serializedObject = $this->serializer->serialize($object);
        $encodedSerializedObject = \mb_convert_encoding($serializedObject, $encoding);
        $stream->write($encodedSerializedObject);
    }
}
