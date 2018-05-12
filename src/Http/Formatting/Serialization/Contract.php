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
 * Defines a contract
 */
abstract class Contract
{
    /** @var string The type of value this contract represents */
    protected $type;
    /** @var Closure The factory that instantiates a value from a decoded value */
    protected $valueFactory;

    /**
     * @param string $type The type of value this contract represents
     * @param Closure $valueFactory The factory that instantiates a value from a decoded value
     */
    public function __construct(string $type, Closure $valueFactory)
    {
        $this->type = $type;
        $this->valueFactory = $valueFactory;
    }

    /**
     * Decodes a value to an instance of the type this contract represents
     *
     * @param mixed $value The value to decode
     * @param IEncodingInterceptor[] $encodingInterceptors The list of encoding interceptors to run through
     * @return mixed An instance of the type this contract represents
     * @throws InvalidArgumentException Thrown if the input value is not of the expected type
     */
    abstract public function decode($value, array $encodingInterceptors = []);

    /**
     * Encodes the input value
     *
     * @param mixed $value The value to encode
     * @param IEncodingInterceptor[] $encodingInterceptors The list of encoding interceptors to run through
     * @return mixed The encoded value
     */
    abstract public function encode($value, array $encodingInterceptors = []);

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
