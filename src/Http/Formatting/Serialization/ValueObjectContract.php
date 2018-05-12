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
 * Defines a value object contract
 */
class ValueObjectContract extends ObjectContract
{
    /** @var Closure The factory that encodes an instance of an object this contract represents */
    protected $encodingFactory;

    /**
     * @inheritdoc
     * @param Closure $encodingFactory The factory that encodes an instance of an object this contract represents
     */
    public function __construct(string $type, Closure $objectFactory, Closure $encodingFactory)
    {
        parent::__construct($type, $objectFactory);

        $this->encodingFactory = $encodingFactory;
    }

    /**
     * @inheritdoc
     */
    public function decode($value): object
    {
        return ($this->objectFactory)($value);
    }

    /**
     * @inheritdoc
     */
    public function encode(object $object)
    {
        return ($this->encodingFactory)($object);
    }
}
