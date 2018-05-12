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
    public function decode($value, array $encodingInterceptors = []): object
    {
        foreach ($encodingInterceptors as $encodingInterceptor) {
            $value = $encodingInterceptor->onDecoding($value, $this->type);
        }

        return ($this->objectFactory)($value);
    }

    /**
     * @inheritdoc
     */
    public function encode(object $object, array $encodingInterceptors = [])
    {
        $encodedValue = ($this->encodingFactory)($object);

        foreach ($encodingInterceptors as $encodingInterceptor) {
            $encodedValue = $encodingInterceptor->onEncoding($encodedValue, $this->type);
        }

        return $encodedValue;
    }
}
