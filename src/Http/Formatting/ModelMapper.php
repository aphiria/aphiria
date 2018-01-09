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
    /** @var ModelMapperRegistry The registry that contains mappers for models */
    private $registry;

    /**
     * @param ModelMapperRegistry $registry The registry that contains mappers for models
     */
    public function __construct(ModelMapperRegistry $registry)
    {
        $this->registry = $registry;
    }

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
        return $this->registry->getFromHashMapper($type)($hash, $this);
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
        return $this->registry->getToHashMapper(get_class($object))($object, $this);
    }
}
