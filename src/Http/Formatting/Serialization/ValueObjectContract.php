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

/**
 * Defines a value contract
 */
class ValueObjectContract extends ObjectContract
{
    /** @var Closure The factory that creates a PHP value from an object */
    protected $phpValueFactory;

    /**
     * @inheritdoc
     * @param Closure $phpValueFactory The factory that creates a PHP value from an object
     */
    public function __construct(string $type, Closure $objectFactory, Closure $phpValueFactory)
    {
        parent::__construct($type, $objectFactory);

        $this->phpValueFactory = $phpValueFactory;
    }

    /**
     * @inheritdoc
     */
    public function createObject($value): object
    {
        return ($this->objectFactory)($value);
    }

    /**
     * @inheritdoc
     */
    public function createPhpValue(object $object)
    {
        return ($this->phpValueFactory)($object);
    }
}
