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
 * Defines the data contract converter
 */
class DataContractConverter implements IDataContractConverter
{
    /** @var DataContractConverterRegistry The registry that contains data contract converters for models */
    private $registry;

    /**
     * @param DataContractConverterRegistry $registry The registry that contains data contract converters
     */
    public function __construct(DataContractConverterRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function convertFromDataContract(string $type, $dataContract)
    {
        return $this->registry->getFromDataContractConverter($type)($dataContract, $this);
    }

    /**
     * @inheritdoc
     */
    public function convertToDataContract($object)
    {
        return $this->registry->getToDataContractConverter(\get_class($object))($object, $this);
    }
}
