<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

use Closure;
use InvalidArgumentException;

/**
 * Defines an object contract
 */
abstract class ObjectContract
{
    /** @var string The type of object this contract represents */
    protected $type;
    /** @var Closure The factory that instantiates an object from a decoded value */
    protected $objectFactory;

    /**
     * @param string $type The type of object this contract represents
     * @param Closure $objectFactory The factory that instantiates an object from a decoded value
     */
    public function __construct(string $type, Closure $objectFactory)
    {
        $this->type = $type;
        $this->objectFactory = $objectFactory;
    }

    /**
     * Decodes a value to an object this contract represents
     *
     * @param mixed $value The value to decode
     * @param IEncodingInterceptor[] $encodingInterceptors The list of encoding interceptors to run through
     * @return \object An instance of the type this contract represents
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     */
    abstract public function decode($value, array $encodingInterceptors = []): object;

    /**
     * Encodes the input object
     *
     * @param \object $object The object to encode
     * @param IEncodingInterceptor[] $encodingInterceptors The list of encoding interceptors to run through
     * @return mixed The encoded object
     */
    abstract public function encode(object $object, array $encodingInterceptors = []);

    /**
     * Gets the type this contract represents
     *
     * @return string The type this contract represents
     */
    public function getType(): string
    {
        return $this->type;
    }
}
