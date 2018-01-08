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

/**
 * Defines the model mapper
 */
class ModelMapper
{
    /** @var array The mapping of object types to to/from hash mappers */
    private $registry = [];

    /**
     * Converts a hash to a model
     *
     * @param string $type The type to convert to
     * @param array $hash The hash to convert
     * @return object An instance of the input type
     * @throws InvalidArgumentException Thrown if no model mapper exists for the input type
     */
    public function convertFromHash(string $type, array $hash)
    {
        if (!isset($this->registry[$type])) {
            throw new InvalidArgumentException("No model mapper registered for type $type");
        }

        return $this->registry[$type][1]($hash, $this);
    }

    /**
     * Converts a model to a hash
     *
     * @param object $object The model to convert
     * @return array The hash
     * @throws InvalidArgumentException Thrown if no model mapper exists for the input model
     */
    public function convertToHash($object) : array
    {
        $type = get_class($object);

        if (!isset($this->registry[$type])) {
            throw new InvalidArgumentException("No model mapper registered for type $type");
        }

        return $this->registry[$type][0]($object, $this);
    }

    /**
     * Registers model mappers for a particular type
     *
     * @param string $type The type whose mappers we're registering
     * @param callable $toHashMapper The hasher that converts a model to a hash (must accept the model and an instance of ModelMapper)
     * @param callable $fromHashMapper The hasher that converts a hash to a model (must accept the hash and an instance of ModelMapper)
     */
    public function registerMapper(string $type, callable $toHashMapper, callable $fromHashMapper) : void
    {
        $this->registry[$type] = [$toHashMapper, $fromHashMapper];
    }
}
