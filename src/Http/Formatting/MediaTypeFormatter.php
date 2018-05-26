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
use Opulence\Serialization\ISerializer;

/**
 * Defines the base class for media type formatters to extend
 */
abstract class MediaTypeFormatter implements IMediaTypeFormatter
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
