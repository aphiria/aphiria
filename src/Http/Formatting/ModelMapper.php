<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

/**
 * Defines the model mapper
 */
class ModelMapper implements IModelMapper
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
     * @inheritdoc
     */
    public function convertFromHash(string $type, array $hash)
    {
        return $this->registry->getFromHashMapper($type)($hash, $this);
    }

    /**
     * @inheritdoc
     */
    public function convertToHash($object) : array
    {
        return $this->registry->getToHashMapper(get_class($object))($object, $this);
    }
}
