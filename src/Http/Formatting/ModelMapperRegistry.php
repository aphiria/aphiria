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
 * Defines a model mapper registry
 */
class ModelMapperRegistry
{
    /** @var array The mapping of types to to/from mappers */
    private $mappers = [];

    /**
     * Gets the mapper that maps a hash to a model
     *
     * @param string $type The type whose mapper we want
     * @return callable The hash-to-model mapper
     * @throws InvalidArgumentException Thrown if no mapper was found for the input type
     */
    public function getFromHashMapper(string $type) : callable
    {
        if (!isset($this->mappers[$type])) {
            throw new InvalidArgumentException("No model mapper found for type $type");
        }

        return $this->mappers[$type]['from'];
    }

    /**
     * Gets the mapper that maps a model to a hash
     *
     * @param string $type The type whose mapper we want
     * @return callable The model-to-hash mapper
     * @throws InvalidArgumentException Thrown if no mapper was found for the input type
     */
    public function getToHashMapper(string $type) : callable
    {
        if (!isset($this->mappers[$type])) {
            throw new InvalidArgumentException("No model mapper found for type $type");
        }

        return $this->mappers[$type]['to'];
    }

    /**
     * Registers model mappers for a particular type
     *
     * @param string $type The type whose mappers we're registering
     * @param callable $toHashMapper The hasher that converts a model to a hash (must accept the model and an instance of ModelMapper)
     * @param callable $fromHashMapper The hasher that converts a hash to a model (must accept the hash and an instance of ModelMapper)
     */
    public function registerMappers(string $type, callable $toHashMapper, callable $fromHashMapper) : void
    {
        $this->mappers[$type] = ['to' => $toHashMapper, 'from' => $fromHashMapper];
    }
}
