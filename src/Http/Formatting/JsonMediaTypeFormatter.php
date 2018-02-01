<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use RuntimeException;

/**
 * Defines the JSON media type formatter
 */
class JsonMediaTypeFormatter implements IMediaTypeFormatter
{
    /** @var array The list of supported character encodings */
    private static $supportedEncodings = ['utf-8'];
    /** @var array The list of supported media types */
    private static $supportedMediaTypes = ['application/json', 'text/json'];
    /** @var IDataContractConverter The data contract converter to use */
    private $dataContractConverter;

    /**
     * @param IDataContractConverter $dataContractConverter The data contract converter to use
     */
    public function __construct(IDataContractConverter $dataContractConverter)
    {
        $this->dataContractConverter = $dataContractConverter;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedEncodings() : array
    {
        return self::$supportedEncodings;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedMediaTypes() : array
    {
        return self::$supportedMediaTypes;
    }

    /**
     * @inheritdoc
     */
    public function readFromStream(string $type, IStream $stream, bool $readAsArrayOfType = false)
    {
        $json = json_decode((string)$stream, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Stream could not be read as JSON');
        }

        if (!$readAsArrayOfType) {
            return $this->convertValueToType($type, $json);
        }

        $values = [];

        foreach ($json as $value) {
            $values[] = $this->convertValueToType($type, $value);
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function writeToStream($object, IStream $stream) : void
    {
        if (is_array($object)) {
            $data = array_map([$this, 'convertToJsonEncodableValue'], $object);
        } else {
            $data = $this->convertToJsonEncodableValue($object);
        }

        $stream->write(json_encode($data));
    }

    /**
     * Converts a value to a JSON-encodable value
     *
     * @param mixed $value The value to convert
     * @return int|double|float|bool|string|array The converted value
     * @throws InvalidArgumentException Thrown if the value could not be converted
     */
    private function convertToJsonEncodableValue($value)
    {
        if (is_scalar($value) || is_array($value) || $value === null) {
            return $value;
        }

        if (is_object($value)) {
            return $this->dataContractConverter->convertToDataContract($value);
        }

        throw new InvalidArgumentException('Expected scalar, array, or object, received ' . gettype($value));
    }

    /**
     * Converts a value to a particular type
     *
     * @param string $type The type to convert to (from gettype() or get_class())
     * @param int|double|float|bool|string|array $value The value to convert
     * @return int|double|float|bool|string|object|null The converted value
     * @throws InvalidArgumentException Thrown if the argument was not one of the accepted types
     */
    private function convertValueToType(string $type, $value)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                return (int)$value;
            case 'double':
                return (double)$value;
            case 'float':
                return (float)$value;
            case 'string':
                return (string)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            default:
                return $this->dataContractConverter->convertFromDataContract($type, $value);
        }
    }
}
